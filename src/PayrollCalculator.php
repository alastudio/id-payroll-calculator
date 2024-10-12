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

namespace Steevenz\IndonesiaPayrollCalculator;

// ------------------------------------------------------------------------

use O2System\Spl\DataStructures\SplArrayObject;
use Steevenz\IndonesiaPayrollCalculator\DataStructures;
use Steevenz\IndonesiaPayrollCalculator\Taxes\NonPph;
use Steevenz\IndonesiaPayrollCalculator\Taxes\Pph21;
use Steevenz\IndonesiaPayrollCalculator\Taxes\Pph23;
use Steevenz\IndonesiaPayrollCalculator\Taxes\Pph26;

/**
 * Class PayrollCalculator
 * @package Steevenz\IndonesiaPayrollCalculator
 */
class PayrollCalculator
{
    /**
     * PayrollCalculator::NETT_CALCULATION
     *
     * PPh 21 ditanggung oleh perusahaan atau penyedia kerja.
     *
     * @var string
     */
    const NETT_CALCULATION = 'NETT';

    /**
     * PayrollCalculator::GROSS_CALCULATION
     *
     * PPh 21 ditanggung oleh pekerja/karyawan.
     *
     * @var string
     */
    const GROSS_CALCULATION = 'GROSS';

    /**
     * PayrollCalculator::gross_percentage
     *
     * first element for company, second element for employee
     *
     * @var array
     */
    public $gross_percentage = [50, 50];

    /**
     * PayrollCalculator::GROSS_UP_CALCULATION
     *
     * Tanggungan PPh 21 ditambahkan sebagai tunjangan pekerja/karyawan.
     *
     * @var string
     */
    const GROSS_UP_CALCULATION = 'GROSSUP';

    /**
     * PayrollCalculator::MIXED_CALCULATION
     *
     * Tanggungan PPh 21 dibagi beban perusahaan dengan karyawan.
     *
     * @var string
     */
    const MIXED_CALCULATION = 'MIXED';

    /**
     * PayrollCalculator::mixed_percentage
     *
     * first element for company, second element for employee
     *
     * @var array
     */
    public $mixed_percentage = [50, 50];

    /**
     * PayrollCalculator::PKWTT
     * Pegawai Tetap
     * 
     * @var string
     */
    const PKWTT = 'PKWTT';

    /**
     * PayrollCalculator::PKWT
     * Pegawai Tidak Tetap
     * 
     * @var string
     */
    const PKWT = 'PKWT';

    /**
     * PayrollCalculator::PKHL
     * Pekerja Harian Lepas
     * 
     * @var string
     */
    const PKHL = 'PKHL';

    /**
     * PayrollCalculator::KEMITRAAN
     * Pegawai kemitraan
     * 
     * @var string
     */
    const KEMITRAAN = 'KEMITRAAN';

    /**
     * PayrollCalculator::$provisions
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Provisions
     */
    public $provisions;

    /**
     * PayrollCalculator::$employee
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee
     */
    public $employee;

    /**
     * PayrollCalculator::$taxNumber
     *
     * @var int
     */
    public $taxNumber = 21;

    /**
     * PayrollCalculator::$method
     *
     * @var string
     */
    public $method = 'NETTO';

    /**
     * PayrollCalculator::$monthlyPositionTaxRate
     * According to Undang-Undang Direktur Jenderal Pajak Nomor PER-32/PJ/2015 Pasal 21 ayat 3
     * Position Deduction is 5% from Annual Gross Income
     * @var int
     */
    public $monthlyPositionTaxRate = 5;

    /**
     * PayrollCalculator::$maxPositionDeductions
     *
     * @var int
     */
    public $maxPositionDeductions = 500000;

    /**
     * PayrollCalculator::$maxPositionDeductions
     *
     * @var int
     */
    public $employeeType = 'PKWTT';

    /**
     * PayrollCalculator::$sallaryPeriod
     * Untuk perhitungan gaji berdasarkan periode gajian BULANAN / MINGGUAN / 2 MINGGUAN (2x Per bulan)
     * @var string
     */
    public $sallaryPeriod = 'BULANAN';

    /**
     * PayrollCalculator::$basedOnPresences
     * Untuk perhitungan gaji berdasarkan jumlah presensi atau tidak
     * @var boolean
     */
    public $basedOnPresences = true;

    /**
     * PayrollCalculator::$berkesinambungan
     * Untuk pekerja KEMITRAAN
     * @var string
     */
    public $berkesinambungan = 'YA';

    /**
     * PayrollCalculator::$cutOff
     * Untuk perhitungan cut off absen
     * @var string
     */
    public $cutOff = 1;

    /**
     * PayrollCalculator::$currentMonth
     * Parameter bulan berjalan
     * @var int
     */
    public $currentMonth = 1;

    /**
     * ByruPayrollCalculator::$companyBpjsKesehatanRate
     *
     * @var int
     */
    public int $companyBpjsKesehatanRate = 4;

    /**
     * ByruPayrollCalculator::$employeeBpjsKesehatanRate
     *
     * @var int
     */
    public $employeeBpjsKesehatanRate = 1;

    /**
     * ByruPayrollCalculator::$jkmRate
     *
     * @var int
     */
    public $jkmRate = 0.30;

    /**
     * ByruPayrollCalculator::$allJhtRate
     *
     * @var int
     */
    public $allJhtRate = 5.7;

    /**
     * ByruPayrollCalculator::$companyJhtRate
     *
     * @var int
     */
    public $companyJhtRate = 3.7;

    /**
     * ByruPayrollCalculator::$employeeJhtRate
     *
     * @var int
     */
    public int $employeeJhtRate = 2;

    /**
     * ByruPayrollCalculator::$allJpRate
     *
     * @var int
     */
    public int $allJpRate = 3;

    /**
     * ByruPayrollCalculator::$companyJpRate
     *
     * @var int
     */
    public int $companyJpRate = 2;

    /**
     * ByruPayrollCalculator::$employeeJpRate
     *
     * @var int
     */
    public $employeeJpRate = 1;

    /**
     * PayrollCalculator::$ter
     * Parameter tarif TER
     * @var array
     * $ter_A = TK/0, TK/1, K/0
     * $ter_B = TK/2, TK/3, K/1, K/2
     * $ter_C = K/3
     */
    public $ter_ptkp_a = ['TK/0', 'TK/1', 'K/0'];
    public $ter_ptkp_b = ['TK/2', 'TK/3', 'K/1', 'K/2'];
    public $ter_ptkp_c = ['K/3'];
    public $ter_A = [];
    public $ter_B = [];
    public $ter_C = [];

    /**
     * PayrollCalculator::$result
     *
     * @var SplArrayObject
     */
    public $result;

    // ------------------------------------------------------------------------

    /**
     * PayrollCalculator::__construct
     *
     * @param array $data
     */
    public function __construct()
    {
        $this->provisions = new DataStructures\Provisions();
        $this->employee = new DataStructures\Employee();
        $this->company = new DataStructures\Company();
        $this->result = new SplArrayObject([
            'earnings'    => new SplArrayObject([
                'base'           => 0,
                'fixedAllowance' => 0,
                'fixedAllowance_taxed' => 0,
                'benefits' => 0,
                'holidayAllowance' => 0,
                'annualy'        => new SplArrayObject([
                    'nett'  => 0,
                    'gross' => 0,
                ]),
            ]),
            'takeHomePay' => 0,
            'employeeType' => '',
            'berkesinambungan' => '',
            'method' => '',
            'params' => $this->employee->params
        ]);
    }

    // ------------------------------------------------------------------------

    /**
     * PayrollCalculator::getCalculation
     *
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    public function getCalculation()
    {
        if ($this->taxNumber == 21) {
            if ($this->sallaryPeriod == 'BULANAN') {
                return $this->calculateBaseOnPph21();
            } elseif ($this->sallaryPeriod == 'MINGGUAN') {
                return $this->calculateBaseOnPph21Weekly();
            } elseif ($this->sallaryPeriod == 'DUA MINGGUAN') {
                return $this->calculateBaseOnPph21Biweekly();
            }
        } elseif ($this->taxNumber == 23) {
            return $this->calculateBaseOnPph23();
        } elseif ($this->taxNumber == 26) {
            return $this->calculateBaseOnPph26();
        } else {
            if ($this->sallaryPeriod == 'BULANAN') {
                return $this->calculateNonPph();
            } elseif ($this->sallaryPeriod == 'MINGGUAN') {
                return $this->calculateNonPphWeekly();
            } elseif ($this->sallaryPeriod == 'DUA MINGGUAN') {
                return $this->calculateNonPphBiweekly();
            }

        }
    }

    // ------------------------------------------------------------------------

    /**
     * PayrollCalculator::calculateOvertimeBasedOnGovRegulation
     * Hitung Lembur Pemerintah
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateOvertimeBasedOnGovRegulation(){
        $salary = $this->employee->earnings->base + $this->employee->earnings->fixedAllowance;;
        $total_ot = 0; 

        if (is_array($this->employee->presences->overtime)) {

            if ($this->provisions->company->workingDaysRule == 5) {
                // 5 Hari Kerja
                foreach($this->employee->presences->overtime as $ot) {
                    if ($ot['hari'] < 6) {
                        // Weekday
                        if ($ot['is_public_holiday']) {
                            // Overtime on public holiday
                            if ($ot['hari'] == 5) {
                                // Public holiday on shortest day
                                if ($ot['jml_jam'] <= 5) {
                                    $total_ot += $ot['jml_jam'] * 2 * 1 / 173 * $salary;
                                } else if ($ot['jml_jam'] > 5 && $ot['jml_jam'] <= 6) {

                                    $overtime1stHours = 5 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 5) * 3 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours;

                                } else if ($ot['jml_jam'] > 6) {

                                    $overtime1stHours = 5 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 5) * 3 * 1 / 173 * $salary;
                                    $overtime3ndHours = ($ot['jml_jam'] - 6) * 4 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours + $overtime3ndHours;

                                }
                            } else {
                                // Public holiday on regular day
                                if ($ot['jml_jam'] <= 8) {
                                    $total_ot += $ot['jml_jam'] * 2 * 1 / 173 * $salary;
                                } else if ($ot['jml_jam'] > 8 && $ot['jml_jam'] <= 9) {

                                    $overtime1stHours = 8 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 8) * 3 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours;

                                } else if ($ot['jml_jam'] > 9) {

                                    $overtime1stHours = 8 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 8) * 3 * 1 / 173 * $salary;
                                    $overtime3ndHours = ($ot['jml_jam'] - 9) * 4 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours + $overtime3ndHours;

                                }
                            }
                        } else {
                            // Overtime on normal weekday
                            if ($ot['jml_jam'] > 1) {
                                $overtime1stHours = 1 * 1.5 * 1 / 173 * $salary;
                                $overtime2ndHours = ($ot['jml_jam'] - 1) * 2 * 1 / 173 * $salary;
                                $total_ot += $overtime1stHours + $overtime2ndHours;

                            } else {
                                $total_ot += $ot['jml_jam'] * 1.5 * 1 / 173 * $salary;
                            }
                        }

                    } else {
                        // Weekend
                        if ($ot['jml_jam'] <= 8) {
                            $total_ot += $ot['jml_jam'] * 2 * 1 / 173 * $salary;
                        } else if ($ot['jml_jam'] > 8 && $ot['jml_jam'] <= 9) {

                            $overtime1stHours = 8 * 2 * 1 / 173 * $salary;
                            $overtime2ndHours = ($ot['jml_jam'] - 8) * 3 * 1 / 173 * $salary;
                            $total_ot += $overtime1stHours + $overtime2ndHours;

                        } else if ($ot['jml_jam'] > 9) {

                            $overtime1stHours = 8 * 2 * 1 / 173 * $salary;
                            $overtime2ndHours = ($ot['jml_jam'] - 8) * 3 * 1 / 173 * $salary;
                            $overtime3ndHours = ($ot['jml_jam'] - 9) * 4 * 1 / 173 * $salary;
                            $total_ot += $overtime1stHours + $overtime2ndHours + $overtime3ndHours;

                        }
                    }
                }
            } else {
                // 6 Hari Kerja
                foreach($this->employee->presences->overtime as $ot) {
                    if ($ot['hari'] < 7) {
                        // Weekday
                        if ($ot['is_public_holiday']) {
                            // Public holiday
                            if ($ot['hari'] == 5) {
                                // Public holiday on shortest day
                                if ($ot['jml_jam'] <= 5) {
                                    $total_ot += $ot['jml_jam'] * 2 * 1 / 173 * $salary;
                                } else if ($ot['jml_jam'] > 5 && $ot['jml_jam'] <= 6) {

                                    $overtime1stHours = 5 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 5) * 3 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours;

                                } else if ($ot['jml_jam'] > 6) {

                                    $overtime1stHours = 5 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 5) * 3 * 1 / 173 * $salary;
                                    $overtime3ndHours = ($ot['jml_jam'] - 6) * 4 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours + $overtime3ndHours;

                                }
                            } else {
                                // Public holiday on regular day
                                if ($ot['jml_jam'] <= 7) {
                                    $total_ot += $ot['jml_jam'] * 2 * 1 / 173 * $salary;
                                } else if ($ot['jml_jam'] > 7 && $ot['jml_jam'] <= 8) {

                                    $overtime1stHours = 7 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 7) * 3 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours;

                                } else if ($ot['jml_jam'] > 8) {

                                    $overtime1stHours = 7 * 2 * 1 / 173 * $salary;
                                    $overtime2ndHours = ($ot['jml_jam'] - 7) * 3 * 1 / 173 * $salary;
                                    $overtime3ndHours = ($ot['jml_jam'] - 8) * 4 * 1 / 173 * $salary;
                                    $total_ot += $overtime1stHours + $overtime2ndHours + $overtime3ndHours;

                                }
                            }
                        } else {
                            if ($ot['jml_jam'] > 1) {
                                $overtime1stHours = 1 * 1.5 * 1 / 173 * $salary;
                                $overtime2ndHours = ($ot['jml_jam'] - 1) * 2 * 1 / 173 * $salary;
                                $total_ot += $overtime1stHours + $overtime2ndHours;

                            } else {
                                $total_ot += $ot['jml_jam'] * 1.5 * 1 / 173 * $salary;
                            }
                        }

                    } else {
                        // Weekend
                        if ($ot['jml_jam'] <= 7) {
                            $total_ot += $ot['jml_jam'] * 2 * 1 / 173 * $salary;
                        } else if ($ot['jml_jam'] > 7 && $ot['jml_jam'] <= 8) {

                            $overtime1stHours = 7 * 2 * 1 / 173 * $salary;
                            $overtime2ndHours = ($ot['jml_jam'] - 7) * 3 * 1 / 173 * $salary;
                            $total_ot += $overtime1stHours + $overtime2ndHours;

                        } else if ($ot['jml_jam'] > 8) {

                            $overtime1stHours = 7 * 2 * 1 / 173 * $salary;
                            $overtime2ndHours = ($ot['jml_jam'] - 7) * 3 * 1 / 173 * $salary;
                            $overtime3ndHours = ($ot['jml_jam'] - 8) * 4 * 1 / 173 * $salary;
                            $total_ot += $overtime1stHours + $overtime2ndHours + $overtime3ndHours;

                        }
                    }
                }
            }

        } else {
            if ($this->employee->presences->overtime > 1) {
                $overtime1stHours = 1 * 1.5 * 1 / 173 * $salary;
                $overtime2ndHours = ($this->employee->presences->overtime - 1) * 2 * 1 / 173 * $salary;
                $total_ot = $overtime1stHours + $overtime2ndHours;
            } else {
                $total_ot = $this->employee->presences->overtime * 1.5 * 1 / 173 * $salary;
            }
        }

        if ($this->employee->presences->fixedOvertime > 0) {
            $additionalOvertime = $this->employee->presences->fixedOvertime * $this->provisions->company->overtimeRate;
            $total_ot = $total_ot + $additionalOvertime;
        }

        return $total_ot;
    }

    /**
     * PayrollCalculator::calculateBaseOnPph21
     * Bulanan
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateBaseOnPph21()
    {
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;
        $this->result->earnings->fixedAllowance_taxed = $this->employee->earnings->fixedAllowance_taxed;;
        $this->result->method = $this->method;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance_taxed;
        $this->result->earnings->salary_gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;
        $this->result->earnings->gross_first = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
            $this->employee->allowances->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        //SET KATEGORI KARYAWAN
        $this->result->employeeType = $this->employeeType;
        $this->result->berkesinambungan = $this->berkesinambungan;

        // Penghasilan tidak teratur
        if ($this->provisions->company->calculateOvertime === true) {
            if($this->provisions->state->overtimeRegulationCalculation) {
                //  Berdasarkan Kepmenakertrans No. 102/MEN/VI/2004
                $this->result->earnings->overtime = $this->calculateOvertimeBasedOnGovRegulation();

            } else {

                $this->result->earnings->overtime = $this->employee->presences->overtimeValue;

                if ($this->employee->presences->fixedOvertime > 0) {
                    $additionalOvertime = $this->employee->presences->fixedOvertime * $this->provisions->company->overtimeRate;
                    $this->result->earnings->overtime = $this->result->earnings->overtime + $additionalOvertime;
                }

            }

            $this->result->earnings->overtime = floor($this->result->earnings->overtime);

            // Lembur ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->overtime;
            $this->result->earnings->salary_gross = $this->result->earnings->salary_gross + $this->result->earnings->overtime;
        }

        if($this->provisions->company->calculateSplitShifts) {
            $this->result->earnings->splitShifts = $this->provisions->company->splitShiftsRate * $this->employee->presences->splitShifts;

            // Split Shift ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->splitShifts;
            $this->result->earnings->salary_gross = $this->result->earnings->salary_gross + $this->result->earnings->splitShifts;
        }

        $this->result->earnings->annualy->gross = $this->result->earnings->gross * 12;
        $this->result->earnings->annualy->gross_salary = $this->result->earnings->gross_salary * 12;

        //Kehadiran
        $this->result->offsetSet('attendance', $this->provisions->company);

        if ($this->employee->permanentStatus === false) {
            $this->company->allowances->BPJSKesehatan = 0;
            $this->employee->deductions->BPJSKesehatan = 0;

            $this->employee->allowances->JKK = 0;
            $this->employee->allowances->JKM = 0;

            $this->employee->allowances->JHT = 0;
            $this->employee->deductions->JHT = 0;

            $this->employee->allowances->JIP = 0;
            $this->employee->deductions->JIP = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('allowances_tax', $this->employee->allowances_tax);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);
            $this->result->offsetSet('deductions_tax', $this->employee->deductions_tax);
            $this->result->offsetSet('benefits_tax', $this->employee->benefits_tax);

            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->benefits_tax->getSum();

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('presence',
                        $this->employee->earnings->base / $this->provisions->company->numOfWorkingDays * $unWork
                    );
                } else {
                    $this->result->deductions->offsetSet('presence', 0);
                }
                */
                $this->result->deductions->offsetSet('presence', 0);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //Kehadiran
            $this->result->offsetSet('attendance', $this->provisions->company);

            //$this->result->offsetSet('taxable', (new Pph21($this))->result);
            $this->result->offsetSet('taxable', (new Pph21($this))->calculate());

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
            $this->result->allowances->offsetSet('positionTax', 0);
            $this->result->allowances->offsetSet('pph21Tax', 0);
        } else {
            if ($this->provisions->company->calculateBPJSKesehatan === true) {
                // Calculate BPJS Kesehatan Allowance & Deduction
                $this->company->allowances->BPJSKesehatan = $this->result->earnings->gross_first * ($this->companyBpjsKesehatanRate / 100);
                $this->employee->deductions->BPJSKesehatan = $this->result->earnings->gross_first * ($this->employeeBpjsKesehatanRate / 100);

                //Added to gross income
                $this->result->earnings->gross = $this->result->earnings->gross + $this->company->allowances->BPJSKesehatan;

                // Maximum number of dependents family is 5
                if ($this->employee->numOfDependentsFamily > 5) {
                    $this->employee->deductions->BPJSKesehatan = $this->employee->deductions->BPJSKesehatan + ($this->employee->deductions->BPJSKesehatan * ($this->employee->numOfDependentsFamily - 5));
                }
            }

            if ($this->provisions->company->JKK === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->company->allowances->JKK = $this->result->earnings->gross_first * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade) / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->company->allowances->JKK = $this->provisions->state->highestWage * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade)/100);
                }

                //Added to gross income
                $this->result->earnings->gross += $this->company->allowances->JKK;
            }

            if ($this->provisions->company->JKM === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->company->allowances->JKM = $this->result->earnings->gross * ($this->jkmRate / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->company->allowances->JKM = $this->provisions->state->highestWage * ($this->jkmRate / 100);
                }

                //Added to gross income
                $this->result->earnings->gross += $this->company->allowances->JKM;
            }

            if ($this->provisions->company->JHT === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross * ($this->allJhtRate / 100);
                        $this->employee->deductions->JHT = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * ($this->allJhtRate / 100);
                        $this->employee->deductions->JHT = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross * ($this->companyJhtRate / 100);
                        $this->employee->deductions->JHT = $this->result->earnings->gross * ($this->employeeJhtRate / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * ($this->companyJhtRate / 100);
                        $this->employee->deductions->JHT = $this->provisions->state->highestWage * ($this->employeeJhtRate / 100);
                    }
                }
                
            }

            if ($this->provisions->company->JIP === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JP = $this->result->earnings->gross_first * ($this->allJpRate / 100);
                        $this->employee->deductions->JP = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JP = $this->provisions->state->highestWageJp * ($this->allJpRate / 100);
                        $this->employee->deductions->JP = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JP = $this->result->earnings->gross_first * ($this->companyJpRate / 100);
                        $this->employee->deductions->JP = $this->result->earnings->gross_first * ($this->employeeJpRate / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JP = $this->provisions->state->highestWageJp * ($this->companyJpRate / 100);
                        $this->employee->deductions->JP = $this->provisions->state->highestWageJp * ($this->employeeJpRate / 100);
                    }
                }
                
            }

            $monthlyPositionTax = 0;

            if ($this->currentMonth == 12) {
                // Last month of current tax year period
                if ($this->result->earnings->gross > $this->provisions->state->provinceMinimumWage) {

                    /**
                     * According to Undang-Undang Direktur Jenderal Pajak Nomor PER-32/PJ/2015 Pasal 21 ayat 3
                     * Position Deduction is 5% from Annual Gross Income
                     */
                    $monthlyPositionTax = $this->result->earnings->gross * ($this->monthlyPositionTaxRate / 100);

                    /**
                     * Maximum Position Deduction in Indonesia is 500000 / month
                     * or 6000000 / year
                     */
                    if ($monthlyPositionTax >= $this->maxPositionDeductions) {
                        $monthlyPositionTax = $this->maxPositionDeductions;
                    }
                }
            } else {
                $monthlyPositionTax = 0;
            }

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('allowances_tax', $this->employee->allowances_tax);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);
            $this->result->offsetSet('deductions_tax', $this->employee->deductions_tax);
            $this->result->offsetSet('benefits', $this->employee->benefits);
            $this->result->offsetSet('benefits_tax', $this->employee->benefits_tax);

            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->benefits_tax->getSum();

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                $this->result->deductions->offsetSet('presence', 0);
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->employee->presences->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum() - $monthlyPositionTax;
            $this->result->earnings->salary_nett = $this->result->earnings->salary_gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            $this->result->offsetSet('taxable', (new Pph21($this))->calculate());
            $this->result->offsetSet('company', $this->company->allowances);

            switch ($this->method) {
                // Pajak ditanggung oleh perusahaan
                case self::NETT_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->salary_nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
                    $this->company->allowances->positionTax = $monthlyPositionTax;
                    $this->company->allowances->pph21Tax = floor($this->result->taxable->liability->monthly);
                    break;
                // Pajak ditanggung oleh karyawan
                case self::GROSS_CALCULATION:
                    //Percentage
                    $companyTax = $this->result->taxable->liability->monthly * ($this->gross_percentage[0] / 100);
                    $employeeTax = $this->result->taxable->liability->monthly * ($this->gross_percentage[1] / 100);

                    $this->result->takeHomePay = $this->result->earnings->salary_nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum() - $this->result->taxable->liability->monthly;
                    $this->result->deductions->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->deductions->offsetSet('pph21Tax', floor($employeeTax));
                    break;
                // Pajak ditanggung oleh perusahaan sebagai tunjangan pajak.
                case self::GROSS_UP_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->salary_nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
                    $this->result->deductions->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->deductions->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    $this->result->allowances->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->allowances->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    break;
                // Beban Pajak dibagi antara perusahaan dengan karyawan
                case self::MIXED_CALCULATION:
                    //Percentage
                    $companyTax = $this->result->taxable->liability->monthly * ($this->mixed_percentage[0] / 100);
                    $employeeTax = $this->result->taxable->liability->monthly * ($this->mixed_percentage[1] / 100);

                    $this->result->takeHomePay = $this->result->earnings->salary_nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum() - $employeeTax;
                    $this->result->deductions->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->deductions->offsetSet('pph21Tax', floor($employeeTax));
                    $this->result->allowances->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->allowances->offsetSet('pph21Tax', floor($companyTax));
                    break;
            }
        }

        return $this->result;
    }

    /**
     * PayrollCalculator::calculateBaseOnPph21Weekly
     * Mingguan
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateBaseOnPph21Weekly()
    {
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;
        $this->result->earnings->gross_first = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
            $this->employee->allowances->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        //SET KATEGORI KARYAWAN
        $this->result->employeeType = $this->employeeType;
        $this->result->berkesinambungan = $this->berkesinambungan;

        $this->result->currentMonth = $this->currentMonth;
        $this->result->ter_ptkp_a = $this->ter_ptkp_a;
        $this->result->ter_ptkp_b = $this->ter_ptkp_b;
        $this->result->ter_ptkp_c = $this->ter_ptkp_c;
        $this->result->ter_A = $this->ter_A;
        $this->result->ter_B = $this->ter_B;
        $this->result->ter_C = $this->ter_C;

        // Penghasilan tidak teratur
        if ($this->provisions->company->calculateOvertime === true) {
            if($this->provisions->state->overtimeRegulationCalculation) {
                //  Berdasarkan Kepmenakertrans No. 102/MEN/VI/2004
                $this->result->earnings->overtime = $this->calculateOvertimeBasedOnGovRegulation();
            } else {
                /*if($this->provisions->company->overtimeRate > 0) {
                    $this->provisions->company->overtimeRate = floor($this->employee->presences->overtime / $this->provisions->company->numOfWorkingDays / $this->provisions->company->numOfWorkingHours);
                }

                $this->result->earnings->overtime = $this->employee->presences->overtime * $this->provisions->company->overtimeRate;
                */
                $this->result->earnings->overtime = $this->employee->presences->overtimeValue;

                if ($this->employee->presences->fixedOvertime > 0) {
                    $additionalOvertime = $this->employee->presences->fixedOvertime * $this->provisions->company->overtimeRate;
                    $this->result->earnings->overtime = $this->result->earnings->overtime + $additionalOvertime;
                }
            }

            $this->result->earnings->overtime = floor($this->result->earnings->overtime);

            // Lembur ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->overtime;
        }

        if($this->provisions->company->calculateSplitShifts) {
            $this->result->earnings->splitShifts = $this->provisions->company->splitShiftsRate * $this->employee->presences->splitShifts;

            // Split Shift ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->splitShifts;
        }

        $this->result->earnings->annualy->gross = $this->result->earnings->gross * 12;

        if ($this->employee->permanentStatus === false) {
            $this->company->allowances->BPJSKesehatan = 0;
            $this->employee->deductions->BPJSKesehatan = 0;

            $this->employee->allowances->JKK = 0;
            $this->employee->allowances->JKM = 0;

            $this->employee->allowances->JHT = 0;
            $this->employee->deductions->JHT = 0;

            $this->employee->allowances->JIP = 0;
            $this->employee->deductions->JIP = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('presence',
                        $this->employee->earnings->base / $this->provisions->company->numOfWorkingDays * $unWork
                    );
                } else {
                    $this->result->deductions->offsetSet('presence', 0);
                }*/
                $this->result->deductions->offsetSet('presence', 0);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = ($this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum()) * 4;
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //$this->result->offsetSet('taxable', (new Pph21($this))->result);
            $this->result->offsetSet('taxable', (new Pph21($this))->calculate());

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
            $this->result->allowances->offsetSet('positionTax', 0);
            $this->result->allowances->offsetSet('pph21Tax', 0);
        } else {
            if ($this->provisions->company->calculateBPJSKesehatan === true) {
                // Calculate BPJS Kesehatan Allowance & Deduction
                $this->company->allowances->BPJSKesehatan = $this->result->earnings->gross_first * ($this->companyBpjsKesehatanRate / 100);
                $this->employee->deductions->BPJSKesehatan = $this->result->earnings->gross_first * ($this->employeeBpjsKesehatanRate / 100);

                // Maximum number of dependents family is 5
                if ($this->employee->numOfDependentsFamily > 5) {
                    $this->employee->deductions->BPJSKesehatan = $this->employee->deductions->BPJSKesehatan + ($this->employee->deductions->BPJSKesehatan * ($this->employee->numOfDependentsFamily - 5));
                }
            }

            if ($this->provisions->company->JKK === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->company->allowances->JKK = $this->result->earnings->gross_first * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade) / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->company->allowances->JKK = $this->provisions->state->highestWage * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade)/100);
                }
            }

            if ($this->provisions->company->JKM === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->company->allowances->JKM = $this->result->earnings->gross * ($this->jkmRate / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->company->allowances->JKM = $this->provisions->state->highestWage * ($this->jkmRate / 100);
                }
            }

            if ($this->provisions->company->JHT === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross * ($this->allJhtRate / 100);
                        $this->employee->deductions->JHT = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * ($this->allJhtRate / 100);
                        $this->employee->deductions->JHT = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross * ($this->companyJhtRate / 100);
                        $this->employee->deductions->JHT = $this->result->earnings->gross * ($this->employeeJhtRate / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * ($this->companyJhtRate / 100);
                        $this->employee->deductions->JHT = $this->provisions->state->highestWage * ($this->employeeJhtRate / 100);
                    }
                }

            }

            if ($this->provisions->company->JIP === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * ($this->allJpRate / 100);
                        $this->employee->deductions->JIP = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * ($this->allJpRate / 100);
                        $this->employee->deductions->JIP = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * ($this->companyJpRate / 100);
                        $this->employee->deductions->JIP = $this->result->earnings->gross_first * ($this->employeeJpRate / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * ($this->companyJpRate / 100);
                        $this->employee->deductions->JIP = $this->provisions->state->highestWageJp * ($this->employeeJpRate / 100);
                    }
                }

            }

            $monthlyPositionTax = 0;
            if ($this->result->earnings->gross > $this->provisions->state->provinceMinimumWage) {

                /**
                 * According to Undang-Undang Direktur Jenderal Pajak Nomor PER-32/PJ/2015 Pasal 21 ayat 3
                 * Position Deduction is 5% from Annual Gross Income
                 */
                $monthlyPositionTax = $this->result->earnings->gross * ($this->monthlyPositionTaxRate / 100);

                /**
                 * Maximum Position Deduction in Indonesia is 500000 / month
                 * or 6000000 / year
                 */
                if ($monthlyPositionTax >= $this->maxPositionDeductions) {
                    $monthlyPositionTax = $this->maxPositionDeductions;
                }
            }

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('presence',
                        $this->employee->earnings->base / $this->provisions->company->numOfWorkingDays * $unWork
                    );
                } else {
                    $this->result->deductions->offsetSet('presence', 0);
                }
                */
                $this->result->deductions->offsetSet('presence', 0);
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->employee->presences->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum() - $monthlyPositionTax;
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;
            $this->result->offsetSet('method', $this->method);

            $this->result->offsetSet('taxable', (new Pph21($this))->calculate());
            $this->result->offsetSet('company', $this->company->allowances);

            switch ($this->method) {
                // Pajak ditanggung oleh perusahaan
                case self::NETT_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->nett + $monthlyPositionTax + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
                    $this->company->allowances->positionTax = $monthlyPositionTax;
                    $this->company->allowances->pph21Tax = $this->result->taxable->liability->monthly;
                    break;
                // Pajak ditanggung oleh karyawan
                case self::GROSS_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->nett + $monthlyPositionTax + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum() - $this->result->taxable->liability->monthly - $monthlyPositionTax;
                    $this->result->deductions->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->deductions->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    break;
                // Pajak ditanggung oleh perusahaan sebagai tunjangan pajak.
                case self::GROSS_UP_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->nett + $monthlyPositionTax + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
                    $this->result->deductions->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->deductions->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    $this->result->allowances->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->allowances->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    break;
            }
        }

        return $this->result;
    }

    /**
     * PayrollCalculator::calculateBaseOnPph21Biweekly
     * 2 mingguan
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateBaseOnPph21Biweekly()
    {
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance_tax;
        $this->result->earnings->gross_first = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
            $this->employee->allowances->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        //SET KATEGORI KARYAWAN
        $this->result->employeeType = $this->employeeType;
        $this->result->berkesinambungan = $this->berkesinambungan;

        // Penghasilan tidak teratur
        if ($this->provisions->company->calculateOvertime === true) {
            if($this->provisions->state->overtimeRegulationCalculation) {
                //  Berdasarkan Kepmenakertrans No. 102/MEN/VI/2004
                $this->result->earnings->overtime = $this->calculateOvertimeBasedOnGovRegulation();
            } else {
                /*if($this->provisions->company->overtimeRate > 0) {
                    $this->provisions->company->overtimeRate = floor($this->employee->presences->overtime / $this->provisions->company->numOfWorkingDays / $this->provisions->company->numOfWorkingHours);
                }

                $this->result->earnings->overtime = $this->employee->presences->overtime * $this->provisions->company->overtimeRate;
                */
                $this->result->earnings->overtime = $this->employee->presences->overtimeValue;

                if ($this->employee->presences->fixedOvertime > 0) {
                    $additionalOvertime = $this->employee->presences->fixedOvertime * $this->provisions->company->overtimeRate;
                    $this->result->earnings->overtime = $this->result->earnings->overtime + $additionalOvertime;
                }
            }

            $this->result->earnings->overtime = floor($this->result->earnings->overtime);

            // Lembur ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->overtime;
        }

        if($this->provisions->company->calculateSplitShifts) {
            $this->result->earnings->splitShifts = $this->provisions->company->splitShiftsRate * $this->employee->presences->splitShifts;

            // Split Shift ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->splitShifts;
        }

        $this->result->earnings->annualy->gross = $this->result->earnings->gross * 12;

        if ($this->employee->permanentStatus === false) {
            $this->company->allowances->BPJSKesehatan = 0;
            $this->employee->deductions->BPJSKesehatan = 0;

            $this->employee->allowances->JKK = 0;
            $this->employee->allowances->JKM = 0;

            $this->employee->allowances->JHT = 0;
            $this->employee->deductions->JHT = 0;

            $this->employee->allowances->JIP = 0;
            $this->employee->deductions->JIP = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('presence',
                        $this->employee->earnings->base / $this->provisions->company->numOfWorkingDays * $unWork
                    );
                } else {
                    $this->result->deductions->offsetSet('presence', 0);
                }
                */

                $this->result->deductions->offsetSet('presence', 0);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //$this->result->offsetSet('taxable', (new Pph21($this))->result);
            $this->result->offsetSet('taxable', (new Pph21($this))->calculate());

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
            $this->result->allowances->offsetSet('positionTax', 0);
            $this->result->allowances->offsetSet('pph21Tax', 0);
        } else {
            if ($this->provisions->company->calculateBPJSKesehatan === true) {
                // Calculate BPJS Kesehatan Allowance & Deduction
                $this->company->allowances->BPJSKesehatan = $this->result->earnings->gross_first * (4 / 100);
                $this->employee->deductions->BPJSKesehatan = $this->result->earnings->gross_first * (1 / 100);

                // Maximum number of dependents family is 5
                if ($this->employee->numOfDependentsFamily > 5) {
                    $this->employee->deductions->BPJSKesehatan = $this->employee->deductions->BPJSKesehatan + ($this->employee->deductions->BPJSKesehatan * ($this->employee->numOfDependentsFamily - 5));
                }
            }

            if ($this->provisions->company->JKK === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->company->allowances->JKK = $this->result->earnings->gross_first * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade) / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->company->allowances->JKK = $this->provisions->state->highestWage * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade)/100);
                }
            }

            if ($this->provisions->company->JKM === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->company->allowances->JKM = $this->result->earnings->gross * (0.30 / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->company->allowances->JKM = $this->provisions->state->highestWage * (0.30 / 100);
                }
            }

            if ($this->provisions->company->JHT === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->result->earnings->gross * (2 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->provisions->state->highestWage * (2 / 100);
                    }
                }

            }

            if ($this->provisions->company->JIP === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (2 / 100);
                        $this->employee->deductions->JIP = $this->result->earnings->gross_first * (1 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (2 / 100);
                        $this->employee->deductions->JIP = $this->provisions->state->highestWageJp * (1 / 100);
                    }
                }

            }

            $monthlyPositionTax = 0;
            if ($this->result->earnings->gross > $this->provisions->state->provinceMinimumWage) {

                /**
                 * According to Undang-Undang Direktur Jenderal Pajak Nomor PER-32/PJ/2015 Pasal 21 ayat 3
                 * Position Deduction is 5% from Annual Gross Income
                 */
                $monthlyPositionTax = $this->result->earnings->gross * (5 / 100);

                /**
                 * Maximum Position Deduction in Indonesia is 500000 / month
                 * or 6000000 / year
                 */
                if ($monthlyPositionTax >= $this->maxPositionDeductions) {
                    $monthlyPositionTax = $this->maxPositionDeductions;
                }
            }

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances_tax);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions_tax);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('presence',
                        $this->employee->earnings->base / $this->provisions->company->numOfWorkingDays * $unWork
                    );
                } else {
                    $this->result->deductions->offsetSet('presence', 0);
                }
                */

                $this->result->deductions->offsetSet('presence', 0);
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->employee->presences->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum() - $monthlyPositionTax;
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            $this->result->offsetSet('taxable', (new Pph21($this))->calculate());
            $this->result->offsetSet('company', $this->company->allowances);

            //return $this->result;

            switch ($this->method) {
                // Pajak ditanggung oleh perusahaan
                case self::NETT_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->nett + $monthlyPositionTax + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
                    //$this->result->allowances->offsetSet('positionTax', $monthlyPositionTax);
                    //$this->result->allowances->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    $this->company->allowances->positionTax = $monthlyPositionTax;
                    $this->company->allowances->pph21Tax = $this->result->taxable->liability->monthly;
                    //$this->company->deductions->BPJSKesehatan
                    break;
                // Pajak ditanggung oleh karyawan
                case self::GROSS_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->nett + $monthlyPositionTax + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum() - $this->result->taxable->liability->monthly - $monthlyPositionTax;
                    $this->result->deductions->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->deductions->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    break;
                // Pajak ditanggung oleh perusahaan sebagai tunjangan pajak.
                case self::GROSS_UP_CALCULATION:
                    $this->result->takeHomePay = $this->result->earnings->nett + $monthlyPositionTax + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
                    $this->result->deductions->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->deductions->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    $this->result->allowances->offsetSet('positionTax', $monthlyPositionTax);
                    $this->result->allowances->offsetSet('pph21Tax', $this->result->taxable->liability->monthly);
                    break;
            }
        }

        return $this->result;
    }

    /**
     * PayrollCalculator::calculateNonPph
     * Bulanan
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateNonPph(){
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;
        $this->result->earnings->gross_first = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
            $this->employee->allowances->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        //SET KATEGORI KARYAWAN
        $this->result->employeeType = $this->employeeType;
        $this->result->berkesinambungan = $this->berkesinambungan;

        // Penghasilan tidak teratur
        if ($this->provisions->company->calculateOvertime === true) {
            if($this->provisions->state->overtimeRegulationCalculation) {
                //  Berdasarkan Kepmenakertrans No. 102/MEN/VI/2004
                $this->result->earnings->overtime = $this->calculateOvertimeBasedOnGovRegulation();
            } else {
                /*if($this->provisions->company->overtimeRate > 0) {
                    $this->provisions->company->overtimeRate = floor($this->employee->presences->overtime / $this->provisions->company->numOfWorkingDays / $this->provisions->company->numOfWorkingHours);
                }

                $this->result->earnings->overtime = $this->employee->presences->overtime * $this->provisions->company->overtimeRate;
                */

                $this->result->earnings->overtime = $this->employee->presences->overtimeValue;

                if ($this->employee->presences->fixedOvertime > 0) {
                    $additionalOvertime = $this->employee->presences->fixedOvertime * $this->provisions->company->overtimeRate;
                    $this->result->earnings->overtime = $this->result->earnings->overtime + $additionalOvertime;
                }
            }

            $this->result->earnings->overtime = floor($this->result->earnings->overtime);

            // Lembur ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->overtime;
        }

        if($this->provisions->company->calculateSplitShifts) {
            $this->result->earnings->splitShifts = $this->provisions->company->splitShiftsRate * $this->employee->presences->splitShifts;

            // Split Shift ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->splitShifts;
        }

        $this->result->earnings->annualy->gross = $this->result->earnings->gross * 12;

        if ($this->employee->permanentStatus === false) {
            $this->company->allowances->BPJSKesehatan = 0;
            $this->employee->deductions->BPJSKesehatan = 0;

            $this->employee->allowances->JKK = 0;
            $this->employee->allowances->JKM = 0;

            $this->employee->allowances->JHT = 0;
            $this->employee->deductions->JHT = 0;

            $this->employee->allowances->JIP = 0;
            $this->employee->deductions->JIP = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    //$this->result->deductions->offsetSet('presence',
                        //$this->employee->earnings->base / $this->provisions->company->numOfWorkingDays * $unWork
                    //);
                    $this->result->deductions->offsetSet('unwork', $unWork);
                } else {
                    //$this->result->deductions->offsetSet('presence', 0);
                    $this->result->deductions->offsetSet('unwork', 0);
                }
                */

                $this->result->deductions->offsetSet('presence', 0);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //Kehadiran
            $this->result->offsetSet('attendance', $this->provisions->company);
            $this->result->offsetSet('company', $this->company->allowances);

            $this->result->offsetSet('taxable', (new NonPph($this))->calculate());

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
            $this->result->allowances->offsetSet('positionTax', 0);
            $this->result->allowances->offsetSet('pph21Tax', 0);
        } else {
            if ($this->provisions->company->calculateBPJSKesehatan === true) {
                // Calculate BPJS Kesehatan Allowance & Deduction
                $this->company->allowances->BPJSKesehatan = $this->result->earnings->gross_first * (4 / 100);
                $this->employee->deductions->BPJSKesehatan = $this->result->earnings->gross_first * (1 / 100);

                // Maximum number of dependents family is 5
                if ($this->employee->numOfDependentsFamily > 5) {
                    $this->employee->deductions->BPJSKesehatan = $this->employee->deductions->BPJSKesehatan + ($this->employee->deductions->BPJSKesehatan * ($this->employee->numOfDependentsFamily - 5));
                }
            }

            if ($this->provisions->company->JKK === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKK = $this->result->earnings->gross_first * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade) / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKK = $this->provisions->state->highestWage * $this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade);
                }
            }

            if ($this->provisions->company->JKM === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKM = $this->result->earnings->gross_first * (0.30 / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKM = $this->provisions->state->highestWage * (0.30 / 100);
                }
            }

            if ($this->provisions->company->JHT === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross_first * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross_first * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->result->earnings->gross_first * (2 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->provisions->state->highestWage * (2 / 100);
                    }
                }

            }

            if ($this->provisions->company->JIP === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (2 / 100);
                        $this->employee->deductions->JIP = $this->result->earnings->gross_first * (1 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (2 / 100);
                        $this->employee->deductions->JIP = $this->provisions->state->highestWageJp * (1 / 100);
                    }
                }

            }

            $monthlyPositionTax = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('unwork', $unWork);
                } else {
                    //$this->result->deductions->offsetSet('presence', 0);
                    $this->result->deductions->offsetSet('unwork', 0);
                }
                */

                $this->result->deductions->offsetSet('presence', 0);
            }

            //Kehadiran
            $this->result->offsetSet('attendance', $this->provisions->company);
            $this->result->offsetSet('kehadiran', $this->employee->presences->workDays);

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum() - $monthlyPositionTax;
            $this->result->earnings->nett_thp = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //$this->result->offsetSet('taxable', (new NonPph($this))->calculate());
            $this->result->offsetSet('company', $this->company->allowances);

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            $this->result->takeHomePay = $this->result->earnings->nett_thp + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();

        }

        return $this->result;
    }

    /**
     * PayrollCalculator::calculateNonPphWeekly
     * Mingguan
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateNonPphWeekly(){
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;
        $this->result->earnings->gross_first = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
            $this->employee->allowances->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        //SET KATEGORI KARYAWAN
        $this->result->employeeType = $this->employeeType;
        $this->result->berkesinambungan = $this->berkesinambungan;

        // Penghasilan tidak teratur
        if ($this->provisions->company->calculateOvertime === true) {
            if($this->provisions->state->overtimeRegulationCalculation) {
                //  Berdasarkan Kepmenakertrans No. 102/MEN/VI/2004
                $this->result->earnings->overtime = $this->calculateOvertimeBasedOnGovRegulation();
            } else {
                /*if($this->provisions->company->overtimeRate > 0) {
                    $this->provisions->company->overtimeRate = floor($this->employee->presences->overtime / $this->provisions->company->numOfWorkingDays / $this->provisions->company->numOfWorkingHours);
                }

                $this->result->earnings->overtime = $this->employee->presences->overtime * $this->provisions->company->overtimeRate;
                */

                $this->result->earnings->overtime = $this->employee->presences->overtimeValue;

                if ($this->employee->presences->fixedOvertime > 0) {
                    $additionalOvertime = $this->employee->presences->fixedOvertime * $this->provisions->company->overtimeRate;
                    $this->result->earnings->overtime = $this->result->earnings->overtime + $additionalOvertime;
                }
            }

            $this->result->earnings->overtime = floor($this->result->earnings->overtime);

            // Lembur ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->overtime;
        }

        if($this->provisions->company->calculateSplitShifts) {
            $this->result->earnings->splitShifts = $this->provisions->company->splitShiftsRate * $this->employee->presences->splitShifts;

            // Split Shift ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->splitShifts;
        }

        $this->result->earnings->annualy->gross = $this->result->earnings->gross * 12;

        if ($this->employee->permanentStatus === false) {
            $this->company->allowances->BPJSKesehatan = 0;
            $this->employee->deductions->BPJSKesehatan = 0;

            $this->employee->allowances->JKK = 0;
            $this->employee->allowances->JKM = 0;

            $this->employee->allowances->JHT = 0;
            $this->employee->deductions->JHT = 0;

            $this->employee->allowances->JIP = 0;
            $this->employee->deductions->JIP = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('unwork', $unWork);
                } else {
                    //$this->result->deductions->offsetSet('presence', 0);
                    $this->result->deductions->offsetSet('unwork', 0);
                }
                */
                $this->result->deductions->offsetSet('presence', 0);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //Kehadiran
            $this->result->offsetSet('attendance', $this->provisions->company);
            $this->result->offsetSet('company', $this->company->allowances);

            //$this->result->offsetSet('taxable', (new Pph21($this))->result);
            $this->result->offsetSet('taxable', (new NonPph($this))->calculate());

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
            $this->result->allowances->offsetSet('positionTax', 0);
            $this->result->allowances->offsetSet('pph21Tax', 0);
        } else {
            if ($this->provisions->company->calculateBPJSKesehatan === true) {
                // Calculate BPJS Kesehatan Allowance & Deduction
                $this->company->allowances->BPJSKesehatan = $this->result->earnings->gross_first * (4 / 100);
                $this->employee->deductions->BPJSKesehatan = $this->result->earnings->gross_first * (1 / 100);

                // Maximum number of dependents family is 5
                if ($this->employee->numOfDependentsFamily > 5) {
                    $this->employee->deductions->BPJSKesehatan = $this->employee->deductions->BPJSKesehatan + ($this->employee->deductions->BPJSKesehatan * ($this->employee->numOfDependentsFamily - 5));
                }
            }

            if ($this->provisions->company->JKK === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKK = $this->result->earnings->gross_first * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade) / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKK = $this->provisions->state->highestWage * $this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade);
                }
            }

            if ($this->provisions->company->JKM === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKM = $this->result->earnings->gross_first * (0.30 / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKM = $this->provisions->state->highestWage * (0.30 / 100);
                }
            }

            if ($this->provisions->company->JHT === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross_first * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross_first * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->result->earnings->gross_first * (2 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->provisions->state->highestWage * (2 / 100);
                    }
                }

            }

            if ($this->provisions->company->JIP === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (2 / 100);
                        $this->employee->deductions->JIP = $this->result->earnings->gross_first * (1 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (2 / 100);
                        $this->employee->deductions->JIP = $this->provisions->state->highestWageJp * (1 / 100);
                    }
                }

            }

            $monthlyPositionTax = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('unwork', $unWork);
                } else {
                    //$this->result->deductions->offsetSet('presence', 0);
                    $this->result->deductions->offsetSet('unwork', 0);
                }
                */
                $this->result->deductions->offsetSet('presence', 0);
            }

            //Kehadiran
            $this->result->offsetSet('attendance', $this->provisions->company);
            $this->result->offsetSet('kehadiran', $this->employee->presences->workDays);

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum() - $monthlyPositionTax;
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //$this->result->offsetSet('taxable', (new NonPph($this))->calculate());
            $this->result->offsetSet('company', $this->company->allowances);

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();

        }

        return $this->result;
    }

    /**
     * PayrollCalculator::calculateNonPphBiweekly
     * 2 Mingguan
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateNonPphBiweekly(){
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;
        $this->result->earnings->gross_first = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
            $this->employee->allowances->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        //SET KATEGORI KARYAWAN
        $this->result->employeeType = $this->employeeType;
        $this->result->berkesinambungan = $this->berkesinambungan;

        // Penghasilan tidak teratur
        if ($this->provisions->company->calculateOvertime === true) {
            if($this->provisions->state->overtimeRegulationCalculation) {
                //  Berdasarkan Kepmenakertrans No. 102/MEN/VI/2004
                $this->result->earnings->overtime = $this->calculateOvertimeBasedOnGovRegulation();
            } else {
                /*if($this->provisions->company->overtimeRate > 0) {
                    $this->provisions->company->overtimeRate = floor($this->employee->presences->overtime / $this->provisions->company->numOfWorkingDays / $this->provisions->company->numOfWorkingHours);
                }

                $this->result->earnings->overtime = $this->employee->presences->overtime * $this->provisions->company->overtimeRate;
                */

                $this->result->earnings->overtime = $this->employee->presences->overtimeValue;

                if ($this->employee->presences->fixedOvertime > 0) {
                    $additionalOvertime = $this->employee->presences->fixedOvertime * $this->provisions->company->overtimeRate;
                    $this->result->earnings->overtime = $this->result->earnings->overtime + $additionalOvertime;
                }
            }

            $this->result->earnings->overtime = floor($this->result->earnings->overtime);

            // Lembur ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->overtime;
        }

        if($this->provisions->company->calculateSplitShifts) {
            $this->result->earnings->splitShifts = $this->provisions->company->splitShiftsRate * $this->employee->presences->splitShifts;

            // Split Shift ditambahkan sebagai pendapatan bruto bulanan
            $this->result->earnings->gross = $this->result->earnings->gross + $this->result->earnings->splitShifts;
        }

        $this->result->earnings->annualy->gross = $this->result->earnings->gross * 12;

        if ($this->employee->permanentStatus === false) {
            $this->company->allowances->BPJSKesehatan = 0;
            $this->employee->deductions->BPJSKesehatan = 0;

            $this->employee->allowances->JKK = 0;
            $this->employee->allowances->JKM = 0;

            $this->employee->allowances->JHT = 0;
            $this->employee->deductions->JHT = 0;

            $this->employee->allowances->JIP = 0;
            $this->employee->deductions->JIP = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('unwork', $unWork);
                } else {
                    //$this->result->deductions->offsetSet('presence', 0);
                    $this->result->deductions->offsetSet('unwork', 0);
                }
                */
                $this->result->deductions->offsetSet('presence', 0);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //Kehadiran
            $this->result->offsetSet('attendance', $this->provisions->company);
            $this->result->offsetSet('company', $this->company->allowances);

            //$this->result->offsetSet('taxable', (new Pph21($this))->result);
            $this->result->offsetSet('taxable', (new NonPph($this))->calculate());

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();
            $this->result->allowances->offsetSet('positionTax', 0);
            $this->result->allowances->offsetSet('pph21Tax', 0);
        } else {
            if ($this->provisions->company->calculateBPJSKesehatan === true) {
                // Calculate BPJS Kesehatan Allowance & Deduction
                $this->company->allowances->BPJSKesehatan = $this->result->earnings->gross_first * (4 / 100);
                $this->employee->deductions->BPJSKesehatan = $this->result->earnings->gross_first * (1 / 100);

                // Maximum number of dependents family is 5
                if ($this->employee->numOfDependentsFamily > 5) {
                    $this->employee->deductions->BPJSKesehatan = $this->employee->deductions->BPJSKesehatan + ($this->employee->deductions->BPJSKesehatan * ($this->employee->numOfDependentsFamily - 5));
                }
            }

            if ($this->provisions->company->JKK === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKK = $this->result->earnings->gross_first * ($this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade) / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKK = $this->provisions->state->highestWage * $this->provisions->state->getJKKRiskGradePercentage($this->provisions->company->riskGrade);
                }
            }

            if ($this->provisions->company->JKM === true) {
                if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKM = $this->result->earnings->gross_first * (0.30 / 100);
                } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                    $this->employee->allowances->JKM = $this->provisions->state->highestWage * (0.30 / 100);
                }
            }

            if ($this->provisions->company->JHT === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross_first * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (5.7 / 100);
                        $this->employee->deductions->JHT = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->result->earnings->gross_first * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->result->earnings->gross_first * (2 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWage) {
                        $this->company->allowances->JHT = $this->provisions->state->highestWage * (3.7 / 100);
                        $this->employee->deductions->JHT = $this->provisions->state->highestWage * (2 / 100);
                    }
                }

            }

            if ($this->provisions->company->JIP === true) {
                if ($this->provisions->company->bpjstk_pay_by_company === true) {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (3 / 100);
                        $this->employee->deductions->JIP = 0;
                    }
                } else {
                    if ($this->result->earnings->gross_first < $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->result->earnings->gross_first * (2 / 100);
                        $this->employee->deductions->JIP = $this->result->earnings->gross_first * (1 / 100);
                    } elseif ($this->result->earnings->gross_first >= $this->provisions->state->provinceMinimumWage && $this->result->earnings->gross_first >= $this->provisions->state->highestWageJp) {
                        $this->company->allowances->JIP = $this->provisions->state->highestWageJp * (2 / 100);
                        $this->employee->deductions->JIP = $this->provisions->state->highestWageJp * (1 / 100);
                    }
                }

            }

            $monthlyPositionTax = 0;

            // Set result allowances, bonus, deductions
            $this->result->offsetSet('allowances', $this->employee->allowances);
            $this->result->offsetSet('bonus', $this->employee->bonus);
            $this->result->offsetSet('deductions', $this->employee->deductions);

            // set deduction presence if not presence
            $unWork = $this->provisions->company->numOfWorkingDays - $this->employee->presences->workDays;

            if ($unWork > 0) {
                /*if ($this->basedOnPresences) {
                    $this->result->deductions->offsetSet('unwork', $unWork);
                } else {
                    //$this->result->deductions->offsetSet('presence', 0);
                    $this->result->deductions->offsetSet('unwork', 0);
                }
                */
                $this->result->deductions->offsetSet('presence', 0);
            }

            //Kehadiran
            $this->result->offsetSet('attendance', $this->provisions->company);
            $this->result->offsetSet('kehadiran', $this->employee->presences->workDays);

            // Tunjangan Hari Raya
            if ($this->employee->earnings->holidayAllowance > 0) {
                $this->result->allowances->offsetSet('holiday', $this->employee->earnings->holidayAllowance);
            }

            // Pendapatan bersih
            $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum() - $monthlyPositionTax;
            $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

            //$this->result->offsetSet('taxable', (new NonPph($this))->calculate());
            $this->result->offsetSet('company', $this->company->allowances);

            // Pengurangan Penalty
            if ($this->basedOnPresences) {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => $this->employee->presences->latePenalty, //$this->employee->presences->latetime * $this->provisions->company->latetimePenalty,
                    'late_nominal' => $this->provisions->company->latePenalty,
                    'absent' => ($this->employee->presences->absentDays * $this->provisions->company->absentPenalty) + $this->employee->presences->absentPenalty,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            } else {
                $this->employee->deductions->offsetSet('penalty', new SplArrayObject([
                    'late'   => 0,
                    'late_nominal' => 0,
                    'absent' => 0,
                    'rule_set' => $this->employee->presences->lateRuleSet
                ]));
            }

            $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum() - $this->employee->deductions->penalty->getSum();

        }

        return $this->result;
    }

    /**
     * PayrollCalculator::calculateBaseOnPph23
     *
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateBaseOnPph23()
    {
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        // Set result allowances, bonus, deductions
        $this->result->offsetSet('allowances', $this->employee->allowances);
        $this->result->offsetSet('bonus', $this->employee->bonus);
        $this->result->offsetSet('deductions', $this->employee->deductions);

        // Pendapatan bersih
        $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
        $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

        $this->result->offsetSet('taxable', (new Pph23($this))->calculate());

        switch ($this->method) {
            // Pajak ditanggung oleh perusahaan
            case self::NETT_CALCULATION:
                $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum();
                break;
            // Pajak ditanggung oleh karyawan
            case self::GROSS_CALCULATION:
                $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->bonus->getSum() - $this->result->taxable->liability->amount;
                $this->result->deductions->offsetSet('pph23Tax', $this->result->taxable->liability->amount);
                break;
            // Pajak ditanggung oleh perusahaan sebagai tunjangan pajak.
            case self::GROSS_UP_CALCULATION:
                $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->bonus->getSum();
                $this->result->allowances->offsetSet('pph23Tax', $this->result->taxable->liability->amount);
                break;
        }

        return $this->result;
    }

    // ------------------------------------------------------------------------

    /**
     * PayrollCalculator::calculateBaseOnPph26
     *
     * @return \O2System\Spl\DataStructures\SplArrayObject
     */
    private function calculateBaseOnPph26()
    {
        // Gaji + Penghasilan teratur
        $this->result->earnings->base = $this->employee->earnings->base;
        $this->result->earnings->fixedAllowance = $this->employee->earnings->fixedAllowance;

        // Penghasilan bruto bulanan merupakan gaji pokok ditambah tunjangan tetap
        $this->result->earnings->gross = $this->result->earnings->base + $this->employee->earnings->fixedAllowance;

        if ($this->employee->calculateHolidayAllowance > 0) {
            $this->result->earnings->holidayAllowance = $this->employee->calculateHolidayAllowance * $this->result->earnings->gross;
        }

        // Set result allowances, bonus, deductions
        $this->result->offsetSet('allowances', $this->employee->allowances);
        $this->result->offsetSet('bonus', $this->employee->bonus);
        $this->result->offsetSet('deductions', $this->employee->deductions);

        // Pendapatan bersih
        $this->result->earnings->nett = $this->result->earnings->gross + $this->result->allowances->getSum() - $this->result->deductions->getSum();
        $this->result->earnings->annualy->nett = $this->result->earnings->nett * 12;

        $this->result->offsetSet('taxable', (new Pph26($this))->calculate());

        switch ($this->method) {
            // Pajak ditanggung oleh perusahaan
            case self::NETT_CALCULATION:
                $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->earnings->holidayAllowance + $this->employee->bonus->getSum();
                break;
            // Pajak ditanggung oleh karyawan
            case self::GROSS_CALCULATION:
                $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->bonus->getSum() - $this->result->taxable->liability->amount;
                $this->result->deductions->offsetSet('pph26Tax', $this->result->taxable->liability->amount);
                break;
            // Pajak ditanggung oleh perusahaan sebagai tunjangan pajak.
            case self::GROSS_UP_CALCULATION:
                $this->result->takeHomePay = $this->result->earnings->nett + $this->employee->bonus->getSum();
                $this->result->allowances->offsetSet('pph26Tax', $this->result->taxable->liability->amount);
                break;
        }

        return $this->result;
    }
}
