<?php
/**
 * This file is part of the Payroll Calculator Package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */

// ------------------------------------------------------------------------

namespace Steevenz\IndonesiaPayrollCalculator\Taxes;

use O2System\Spl\DataStructures\SplArrayObject;

/**
 * Class Pph21
 * @package Steevenz\IndonesiaPayrollCalculator\Taxes
 */
class Pph21 extends AbstractPph
{
    /**
     * PPh21::calculate
     *
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    public function calculate()
    {
        /**
         * PPh21 dikenakan bagi yang memiliki penghasilan lebih dari 4500000
         */
        if ($this->calculator->result->employeeType == 'PKWTT' || $this->calculator->result->employeeType == 'PKWT') {
            if($this->calculator->result->earnings->nett > 4500000) {
                // Annual PTKP base on number of dependents family
                $this->result->ptkp->amount = $this->calculator->provisions->state->getPtkpAmount($this->calculator->employee->numOfDependentsFamily, $this->calculator->employee->maritalStatus);

                // Annual PKP (Pajak Atas Upah)
                if($this->calculator->employee->earnings->holidayAllowance > 0 && $this->calculator->employee->bonus->getSum() == 0) {
                    // Pajak Atas Upah
                    $earningTax = ($this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount) * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                    // Penghasilan + THR Kena Pajak
                    $this->result->pkp = ($this->calculator->result->earnings->annualy->nett + $this->calculator->employee->earnings->holidayAllowance) - $this->result->ptkp->amount;

                    $this->result->liability->annual = $this->result->pkp - $earningTax;
                } elseif($this->calculator->employee->earnings->holidayAllowance > 0 && $this->calculator->employee->bonus->getSum() > 0) {
                    // Pajak Atas Upah
                    $earningTax = ($this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount) * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                    // Penghasilan + THR Kena Pajak
                    $this->result->pkp = ($this->calculator->result->earnings->annualy->nett + $this->calculator->employee->earnings->holidayAllowance + $this->calculator->employee->bonus->getSum()) - $this->result->ptkp->amount;
                    $this->result->liability->annual = $this->result->pkp - $earningTax;
                } else {
                    $this->result->pkp = $this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount;
                    $this->result->liability->annual = $this->result->pkp * ($this->getRate($this->calculator->result->earnings->nett) / 100);
                }

                if($this->result->liability->annual > 0) {
                    // Jika tidak memiliki NPWP dikenakan tambahan 20%
                    if($this->calculator->employee->hasNPWP === false) {
                        $this->result->liability->annual = $this->result->liability->annual + ($this->result->liability->annual * (20/100));
                    }

                    $this->result->liability->monthly = $this->result->liability->annual / 12;
                    $this->result->liability->weekly = $this->result->liability->monthly / 4;
                } else {
                    $this->result->liability->annual = 0;
                    $this->result->liability->monthly = 0;
                    $this->result->liability->weekly = 0;
                }
            }
        } else if ($this->calculator->result->employeeType == 'PKHL') {
            if ($this->calculator->result->earnings->base < 450000) {
                $minEarnings = 4500000; //Akumulasi pendapatan minimal dalam 1 bulan gaji
                $totalEarnings = 0;
                $totalWorkDays = $this->calculator->employee->presences->workDays;
                $actualTax = 0;

                for ($i = 1; $i <= $totalWorkDays; $i++) {
                    $totalEarnings = $totalEarnings + $this->calculator->result->earnings->base;

                    if ($totalEarnings > $minEarnings) {
                        $this->result->ptkp->amount = $this->calculator->provisions->state->getPtkpAmount($this->calculator->employee->numOfDependentsFamily, $this->calculator->employee->maritalStatus);
                        $realptkp = $i * ($this->result->ptkp->amount / 360);

                        $first_pkp = $totalEarnings - $realptkp;
                        $first_pph = $first_pkp * ($this->getRate($this->calculator->result->earnings->nett) / 100);
                        $actualTax = $actualTax + $first_pph;

                        //$this->result->pkp = $this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount;

                        if ($totalWorkDays > $i) {
                            $dailyptkp = $this->result->ptkp->amount / 360;
                            $next_pkp = $this->calculator->result->earnings->base - $dailyptkp;
                            $next_pph = (($totalWorkDays - $i) * $next_pkp) * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                            $actualTax = $actualTax + $next_pph;
                        }

                        break;
                    }
                }

                $this->result->liability->annual = 0;
                $this->result->liability->monthly = $actualTax;
                $this->result->liability->weekly = $actualTax/4;

                // Jika tidak memiliki NPWP dikenakan tambahan 20%
                if($this->calculator->employee->hasNPWP === false) {
                    $this->result->liability->monthly = $actualTax + ($actualTax * (20/100));
                    $this->result->liability->weekly = $this->result->liability->monthly/4;
                }


            } else {
                $dailypkp       = $this->calculator->result->earnings->base - 450000;
                $minEarnings    = 4500000; //Akumulasi pendapatan minimal dalam 1 bulan gaji
                $totalEarnings  = 0;
                $totalWorkDays  = $this->calculator->employee->presences->workDays;
                $firstTax       = 0;
                $actualTax      = 0;

                for ($i = 1; $i <= $totalWorkDays; $i++) {
                    $totalEarnings = $totalEarnings + $this->calculator->result->earnings->base;

                    $firstTax = $firstTax + ($dailypkp * ($this->getRate($this->calculator->result->earnings->nett) / 100));
                    $actualTax = $actualTax + $firstTax;

                    if ($totalEarnings > $minEarnings) {
                        $this->result->ptkp->amount = $this->calculator->provisions->state->getPtkpAmount($this->calculator->employee->numOfDependentsFamily, $this->calculator->employee->maritalStatus);
                        $realptkp = $i * ($this->result->ptkp->amount / 360);
                        $first_pkp = $totalEarnings - $realptkp;
                        $first_pph = $first_pkp * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                        break;
                    }
                }

                if($this->result->liability->annual > 0) {
                    // Jika tidak memiliki NPWP dikenakan tambahan 20%
                    if($this->calculator->employee->hasNPWP === false) {
                        $this->result->liability->annual = $this->result->liability->annual + ($this->result->liability->annual * (20/100));
                    }

                    $this->result->liability->monthly = $this->result->liability->annual / 12;
                    $this->result->liability->weekly = $this->result->liability->monthly / 4;
                } else {
                    $this->result->liability->annual = 0;
                    $this->result->liability->monthly = 0;
                    $this->result->liability->weekly = 0;
                }

            }
        } else if ($this->calculator->result->employeeType == 'KEMITRAAN') {
            if ($this->calculator->result->berkesinambungan == 'YA') {
                // Annual PTKP base on number of dependents family
                $this->result->ptkp->amount = $this->calculator->provisions->state->getPtkpAmount($this->calculator->employee->numOfDependentsFamily, $this->calculator->employee->maritalStatus);

                // Annual PKP (Pajak Atas Upah)
                if($this->calculator->employee->earnings->holidayAllowance > 0 && $this->calculator->employee->bonus->getSum() == 0) {
                    // Pajak Atas Upah
                    $earningTax = (($this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount)/2) * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                    // Penghasilan + THR Kena Pajak
                    $this->result->pkp = ($this->calculator->result->earnings->annualy->nett + $this->calculator->employee->earnings->holidayAllowance) - $this->result->ptkp->amount;

                    $this->result->liability->annual = $this->result->pkp - $earningTax;
                } elseif($this->calculator->employee->earnings->holidayAllowance > 0 && $this->calculator->employee->bonus->getSum() > 0) {
                    // Pajak Atas Upah
                    $earningTax = (($this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount)/2) * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                    // Penghasilan + THR Kena Pajak
                    $this->result->pkp = ($this->calculator->result->earnings->annualy->nett + $this->calculator->employee->earnings->holidayAllowance + $this->calculator->employee->bonus->getSum()) - $this->result->ptkp->amount;
                    $this->result->liability->annual = $this->result->pkp - $earningTax;
                } else {
                    $this->result->pkp = $this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount;
                    $this->result->liability->annual = ($this->result->pkp/2) * ($this->getRate($this->calculator->result->earnings->nett) / 100);
                }

                if($this->result->liability->annual > 0) {
                    // Jika tidak memiliki NPWP dikenakan tambahan 20%
                    if($this->calculator->employee->hasNPWP === false) {
                        $this->result->liability->annual = $this->result->liability->annual + ($this->result->liability->annual * (20/100));
                    }

                    $this->result->liability->monthly = $this->result->liability->annual / 12;
                    $this->result->liability->weekly = $this->result->liability->monthly / 4;
                } else {
                    $this->result->liability->annual = 0;
                    $this->result->liability->monthly = 0;
                    $this->result->liability->weekly = 0;
                }
            } else {
                // Annual PTKP base on number of dependents family
                $this->result->ptkp->amount = 0;

                // Annual PKP (Pajak Atas Upah)
                if($this->calculator->employee->earnings->holidayAllowance > 0 && $this->calculator->employee->bonus->getSum() == 0) {
                    // Pajak Atas Upah
                    $earningTax = (($this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount)/2) * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                    // Penghasilan + THR Kena Pajak
                    $this->result->pkp = ($this->calculator->result->earnings->annualy->nett + $this->calculator->employee->earnings->holidayAllowance) - $this->result->ptkp->amount;

                    $this->result->liability->annual = $this->result->pkp - $earningTax;
                } elseif($this->calculator->employee->earnings->holidayAllowance > 0 && $this->calculator->employee->bonus->getSum() > 0) {
                    // Pajak Atas Upah
                    $earningTax = (($this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount)/2) * ($this->getRate($this->calculator->result->earnings->nett) / 100);

                    // Penghasilan + THR Kena Pajak
                    $this->result->pkp = ($this->calculator->result->earnings->annualy->nett + $this->calculator->employee->earnings->holidayAllowance + $this->calculator->employee->bonus->getSum()) - $this->result->ptkp->amount;
                    $this->result->liability->annual = $this->result->pkp - $earningTax;
                } else {
                    $this->result->pkp = $this->calculator->result->earnings->annualy->nett - $this->result->ptkp->amount;
                    $this->result->liability->annual = ($this->result->pkp/2) * ($this->getRate($this->calculator->result->earnings->nett) / 100);
                }

                if($this->result->liability->annual > 0) {
                    // Jika tidak memiliki NPWP dikenakan tambahan 20%
                    if($this->calculator->employee->hasNPWP === false) {
                        $this->result->liability->annual = $this->result->liability->annual + ($this->result->liability->annual * (20/100));
                    }

                    $this->result->liability->monthly = $this->result->liability->annual / 12;
                    $this->result->liability->weekly = $this->result->liability->monthly / 4;
                } else {
                    $this->result->liability->annual = 0;
                    $this->result->liability->monthly = 0;
                    $this->result->liability->weekly = 0;
                }
            }
        }


        return $this->result;
    }
}
