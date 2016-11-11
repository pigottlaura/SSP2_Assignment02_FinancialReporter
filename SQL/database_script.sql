use SSP2_assignment02;
SET GLOBAL sql_mode = STRICT_ALL_TABLES;

CREATE TABLE expense_category (
	id INT(10) AUTO_INCREMENT,
	name VARCHAR(20),
	CONSTRAINT expense_category_pk PRIMARY KEY(id)
);

CREATE TABLE expense (
	id INT(10) AUTO_INCREMENT,
	employee_id INT(20) NOT NULL,
	category INT(10) NOT NULL,
	receipt VARCHAR(40),
	cost DECIMAL(8, 2) NOT NULL,
	description TEXT NOT NULL,
	approved BOOLEAN DEFAULT FALSE,
	date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	date_approved TIMESTAMP,
	CONSTRAINT expense_employee_fk FOREIGN KEY(employee_id) REFERENCES wp_users(id),
	CONSTRAINT expense_category_fk FOREIGN KEY(category) REFERENCES expense_category(id),
	CONSTRAINT expense_pk PRIMARY KEY (id)
);

INSERT INTO expense_category(name) VALUES("Food");
INSERT INTO expense_category(name) VALUES("Petrol");
INSERT INTO expense_category(name) VALUES("Accomodation");
INSERT INTO expense_category(name) VALUES("Transport");
INSERT INTO expense_category(name) VALUES("Other");


