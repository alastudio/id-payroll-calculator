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

namespace Steevenz\IndonesiaPayrollCalculator\DataStructures;

// ------------------------------------------------------------------------

/**
 * Class Employee
 * @package Steevenz\IndonesiaPayrollCalculator\DataStructures
 */
class Employee
{
    /**
     * Employee::$permanentStatus
     *
     * @var bool
     */
    public $permanentStatus = true;
    
    /**
     * Employee::$maritalStatus
     *
     * @var bool
     */
    public $maritalStatus = false;

    /**
     * Employee::$hasNPWP
     *
     * @var bool
     */
    public $hasNPWP = true;

    /**
     * Employee::$numOfDependentsFamily
     *
     * @var int
     */
    public $numOfDependentsFamily = 0;

    /**
     * Employee::$presences
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee\Presences
     */
    public $presences;

    /**
     * Employee::$earnings
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee\Earnings
     */
    public $earnings;

    /**
     * Employee::$allowances
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee\Allowances
     */
    public $allowances;

    /**
     * Employee::$allowances_tax
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee\Allowances
     */
    public $allowances_tax;

    /**
     * Employee::$deductions
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee\Deductions
     */
    public $deductions;

    /**
     * Employee::$deductions_tax
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee\Deductions
     */
    public $deductions_tax;

    /**
     * Employee::$benefits
     *
     * @
     */
    public $benefits;

    /**
     * Employee::$benefits_tax
     *
     * @
     */
    public $benefits_tax;

    /**
     * Company::$calculateHolidayAllowance
     *
     * @var int
     */
    public $calculateHolidayAllowance = 0;

    /**
     * Employee::$bonus
     *
     * @var \Steevenz\IndonesiaPayrollCalculator\DataStructures\Employee\Bonus
     */
    public $bonus;

    public $params;

    // ------------------------------------------------------------------------

    /**
     * Employee::__construct
     */
    public function __construct()
    {
        $this->params = new Employee\Params();
        $this->presences = new Employee\Presences();
        $this->earnings = new Employee\Earnings();
        $this->allowances = new Employee\Allowances();
        $this->allowances_tax = new Employee\Allowances_tax();
        $this->deductions = new Employee\Deductions();
        $this->deductions_tax = new Employee\Deductions_tax();
        $this->benefits = new Employee\Benefits();
        $this->benefits_tax = new Employee\Benefits_tax();
        $this->bonus = new Employee\Bonus();
    }
}