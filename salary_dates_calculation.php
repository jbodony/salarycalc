<?php

/**
 * The company’s payroll system currently gives Sales staff two payments every month. They
 * receive a regular fixed base monthly salary, plus a monthly bonus, with the following rules for their
 * issuing dates:
 *
 * The base salaries are paid on the last day of the month, unless that day is a Saturday or
 * a Sunday (weekend). In that case, salaries are paid on the Friday before the weekend.
 * On the 15 th of every month bonuses are paid for the previous month, unless that day is a
 * weekend. In that case, they are paid the first Wednesday after the 15 th .
 * For the sake of this challenge, please do not take into account public holidays.
 * The Administration Department at this point in time needs the output of the utility to be a CSV
 * file, containing the payment dates for the next twelve months, starting with the current month.
 *
 * The CSV file contains the following columns:
 * the month name
 * the salary payment date for that month
 * the bonus payment date for the previous month.
 *
 * The output file name should be provided as an argument to the CLI command.
 * CLI command format:
 * php salary_dates_calculation.php salary_dates.csv
 */

/**
 * Class for file related tasks.
 */
class FileDates
{

  public $filename;

  function __construct($arg)
  {
    // Get the filename. It is the first argument.
    $this->filename = $arg;
  }

  /**
   * Checks the filename and gives ".csv" extension if it is necessary.
   *
   * @return string filename with .csv or FALSE if the string is not a proper filename.
   */
  function getFilename()
  {
    return $this->is_valid_filename($this->filename) ? $this->filename = $this->add_csv_filename($this->filename) : FALSE;
  }

  /**
   * Stores the calculated salary dates.
   */
  function storeSalaryDates($salary_dates)
  {

    // Stores the calculated payment days into the filename.
    $fp = fopen($this->filename, 'w');

    foreach ($salary_dates as $salary_date) {
      fputcsv($fp, $salary_date);
    }

    fclose($fp);
    echo "The file with 'Salary dates' data has been saved.";
  }

  /**
   * Helper function to check the given filename.
   *
   * @param string $filename The given filename
   * @return false|int regular expression match: 1, FALSE if the string is not a proper filename.
   */
  protected function is_valid_filename(string $filename)
  {
    return preg_match('/^([-\.\w]+)$/', $filename);
  }

  /**
   * Helper function to add ".csv" extension to the filename if it is missing.
   *
   * @param string $filename The given filename
   * @return string $filename with ".csv" extension.
   */
  protected function add_csv_filename(string $filename): string
  {
    if (pathinfo($filename, PATHINFO_EXTENSION) != 'csv') {
      $filename .= '.csv';
    }
    return $filename;
  }
}

/**
 * Class for salary calculation.
 */
class SalaryDates
{

  /**
   * Changeable constants:
   *
   * SALARY_PAYMENT_OPTIONAL_WEEKDAY:
   *  When should be the salary payment if the latest day of the month is a weekend day.
   *  Values should be between 1 and 5  [Monday -> Friday].  Default value is 5.
   * BONUS_PAYMENT_DATE:
   *  When should be the bonus payment in a month. Default value is 15.
   * BONUS_PAYMENT_OPTIONAL_WEEKDAY:
   *  When should be the bonus payment if the bonus payment day of the month is a weekend day.
   *  Values should be between 1 and 5  [Monday -> Friday on next week].  Default value is 3 (Wednesday).
   * MONTH_NUMBER:
   *  Number of the calculated payment days.
   *
   * @author Janos Bodony <mail@bodony.com>
   */

  const SALARY_PAYMENT_OPTIONAL_WEEKDAY = 5;
  const BONUS_PAYMENT_DATE = 15;
  const BONUS_PAYMENT_OPTIONAL_WEEKDAY = 3;
  const MONTH_NUMBER = 12;

  protected array $salary_dates;
  protected datetime $firstDayMonth;

  function __construct()
  {
    // Define the first row of the csv file.
    $this->salary_dates = [['month name', 'salary payment date', 'bonus payment date']];
    // Get the first day of the current month.
    try {
      $this->firstDayMonth = new DateTime(date('Y-m-01'));
    } catch (Exception $e) {
      echo $e->getMessage();
      exit(1);
    }
  }

  /**
   * Calculates payment days.
   * @return array payment dates
   */
  function get_payment_days(): array
  {
    // Calculates the payment days in a loop and stores into an array.
    for ($i = 0; $i < self::MONTH_NUMBER; $i++) {
      // Get the payment day.
      $salary_payment_day = self::get_salary_payment_day($this->firstDayMonth);
      // Get the bonus day.
      $bonusPaymentDay = self::get_bonus_payment_day($this->firstDayMonth);
      // Get the English name of the given month.
      $monthName = $this->firstDayMonth->format('F');
      // Stores the name of the month and the calculated dates.
      $this->salary_dates[] = [$monthName, $salary_payment_day->format('d/m/Y'), $bonusPaymentDay->format('d/m/Y')];
      $this->firstDayMonth = self::add_month($this->firstDayMonth);
    }
    return $this->salary_dates;
  }

  /**
   * Helper function to check the given date is a weekend day or not.
   *
   * @param datetime $date what should be checked
   * @return string|false number of weekend days (6 - Saturday, 7 - Sunday) or FALSE if the date is not a weeekend day.
   * @throws
   */
  protected function is_weekend(datetime $date)
  {
    try {
      $dateTime = new DateTime($date->format('Y-m-d'));
    } catch (Exception $e) {
      echo $e->getMessage();
      exit(1);
    }
    return $dateTime->format('N') > 5 ? $dateTime->format('N') : FALSE;
  }

  /**
   * Helper function to calculate the last date of the given month.
   *
   * @param datetime $date what should be checked
   * @return datetime last day of the give month.
   * @throws
   */
  protected function last_day_month(datetime $date): datetime
  {
    try {
      $dateTime = new DateTime($date->format('Y-m-d'));
    } catch (Exception $e) {
      echo $e->getMessage();
      exit(1);
    }
    return $dateTime->modify('last day of this month');
  }

  /**
   * Helper function to calculate the next month.
   *
   * @param datetime $dateTime The given month
   * @return datetime $dateTime  The next month.
   * @throws
   */
  protected function add_month(datetime $dateTime): datetime
  {
    $oldDay = $dateTime->format("d");
    $dateTime->add(new DateInterval("P1M"));
    $newDay = $dateTime->format("d");

    // If the next month's day is different,
    // we should present the last day of the next month.
    if ($oldDay != $newDay) {
      $dateTime->sub(new DateInterval("P" . $newDay . "D"));
    }
    return $dateTime;
  }

  /**
   * Helper function to calculate the salary payment day.
   *
   * @param datetime $firstDayMonth The first day of the given month
   * @return datetime $salaryPaymentDay The calculated salary payment day.
   * @throws
   */
  protected function get_salary_payment_day(datetime $firstDayMonth): datetime
  {
    $salaryPaymentDay = self::last_day_month($firstDayMonth);
    if ($weekendday = self::is_weekend($salaryPaymentDay)) {
      // If the date is a weekend day,
      // choose the last Friday or the last given weekday.
      $weekendday -= self::SALARY_PAYMENT_OPTIONAL_WEEKDAY;
      $salaryPaymentDay->sub(new DateInterval("P" . $weekendday . "D"));
    }
    return $salaryPaymentDay;
  }

  /**
   * Helper function to calculate the bonus payment day.
   *
   * @param datetime $firstDayMonth The first day of the given month
   * @return datetime $bonusPaymentDay The calculated bonus payment day.
   * @throws
   */
  protected function get_bonus_payment_day(datetime $firstDayMonth): datetime
  {
    try {
      $bonusPaymentDay = new DateTime($firstDayMonth->format('Y-m-' . self::BONUS_PAYMENT_DATE));
      // TODO:  Check and calculate proper dates if BONUS_PAYMENT_DATE > 28
    } catch (Exception $e) {
      echo $e->getMessage();
      exit(1);
    }

    if ($weekendday = self::is_weekend($bonusPaymentDay)) {
      // If the date is a weekend day,
      // choose the next Wednesday or the given weekday.
      $weekendday = (7 - $weekendday) + self::BONUS_PAYMENT_OPTIONAL_WEEKDAY;
      $bonusPaymentDay->add(new DateInterval("P" . $weekendday . "D"));
    }
    return $bonusPaymentDay;
  }
}

/**
 * Payment date calculation.
 * It gets filename from the argument, calculates the payment dates and put into the 'filename.csv' file.
 */
$filename = $argv[1] ?? "salary_dates.csv";

$filedate = new FileDates($filename);

if ($filedate->getFilename()) {
  $salarydate = new SalaryDates();
  $salary_dates = $salarydate->get_payment_days();
  $filedate->storeSalaryDates($salary_dates);
} else {
  echo "\nThe filename is invalid.\n";
}
?>

