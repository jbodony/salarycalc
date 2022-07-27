# features/salarycalctest.feature
Feature: salarycalc
  In order to check the salary calculation in July and August
  I need to run alary_dates_calculation.php script
  I need to check the output

  Scenario: Checks the calculated paydays in July.
    Given I have a file named "salary_dates_calculation.php"
    When I run "php salary_dates_calculation.php salary_dates.csv"
    Then I should get a file named "salary_dates.csv"
    And File should contain:
"""
"month name","salary payment date","bonus payment date"
July,29/07/2022,15/07/2022
August,31/08/2022,15/08/2022
September,30/09/2022,15/09/2022
October,31/10/2022,19/10/2022
November,30/11/2022,15/11/2022
December,30/12/2022,15/12/2022
January,31/01/2023,18/01/2023
February,28/02/2023,15/02/2023
March,31/03/2023,15/03/2023
April,28/04/2023,19/04/2023
May,31/05/2023,15/05/2023
June,30/06/2023,15/06/2023
"""

  Scenario: Checks the calculated in August.
    Given I have a file named "salary_dates_calculation.php"
    When I run "php salary_dates_calculation.php salary_dates.csv"
    Then I should get a file named "salary_dates.csv"
    And File should contain:
"""
"month name","salary payment date","bonus payment date"
August,31/08/2022,15/08/2022
September,30/09/2022,15/09/2022
October,31/10/2022,19/10/2022
November,30/11/2022,15/11/2022
December,30/12/2022,15/12/2022
January,31/01/2023,18/01/2023
February,28/02/2023,15/02/2023
March,31/03/2023,15/03/2023
April,28/04/2023,19/04/2023
May,31/05/2023,15/05/2023
June,30/06/2023,15/06/2023
July,31/07/2023,19/07/2023
"""



