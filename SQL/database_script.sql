use SSP2_assignment02;
SET GLOBAL sql_mode = STRICT_ALL_TABLES;

CREATE TABLE lp_financialReporter_expense_category (
  id INT(10) AUTO_INCREMENT,
  name VARCHAR(20),
  CONSTRAINT expense_category_pk PRIMARY KEY(id)
);

CREATE TABLE lp_financialReporter_expense (
  id INT(10) AUTO_INCREMENT,
  employee_id BIGINT(20) UNSIGNED NOT NULL,
  category INT(10) NOT NULL,
  receipt VARCHAR(125),
  cost DECIMAL(8, 2) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  decision_date TIMESTAMP,
  CONSTRAINT expense_employee_fk FOREIGN KEY(employee_id) REFERENCES wp_users(id),
  CONSTRAINT expense_category_fk FOREIGN KEY(category) REFERENCES lp_financialReporter_expense_category(id),
  CONSTRAINT expense_pk PRIMARY KEY (id)
);

INSERT INTO lp_financialReporter_expense_category(name) VALUES('Food');
INSERT INTO lp_financialReporter_expense_category(name) VALUES('Petrol');
INSERT INTO lp_financialReporter_expense_category(name) VALUES('Accomodation');
INSERT INTO lp_financialReporter_expense_category(name) VALUES('Transport');
INSERT INTO lp_financialReporter_expense_category(name) VALUES('Other');
