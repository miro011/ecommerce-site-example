/****************************************************************************/
DROP TABLE IF EXISTS `globals`;

CREATE TABLE `globals` (
    `attr` VARCHAR(20), # the name of the global var (for ex: admin, email_server, alt_com_email, paypal_skey, stripe_skey)
    `value` VARCHAR(100), # the value (blah@gmail.com)
    
    PRIMARY KEY (attr)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO globals VALUES ("admin", "admin@admin.com");
INSERT INTO globals VALUES ("site", "http://localhost/");
INSERT INTO globals VALUES ("site_short", "localhost");
INSERT INTO globals VALUES ("alt_com_email", "");
INSERT INTO globals VALUES ("email_server", "");
INSERT INTO globals VALUES ("skey_gcapthca", "");
INSERT INTO globals VALUES ("skey_paypal", "");
INSERT INTO globals VALUES ("skey_stripe", "");

/****************************************************************************/
DROP TABLE IF EXISTS `siteinfo`;

CREATE TABLE `siteinfo` (
    `sectionid` INT NOT NULL,
    `sectionname` VARCHAR(50) NOT NULL,
	`html` TEXT NOT NULL,
    
    PRIMARY KEY (sectionid)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO siteinfo VALUES(1,"news","type news info here");
INSERT INTO siteinfo VALUES(2,"about","type about here");
INSERT INTO siteinfo VALUES(3,"privacy","type privacy info here");


/****************************************************************************/
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
	`userid` INT NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(50) NOT NULL,
	`password` VARCHAR(100) NOT NULL,
    `firstname` VARCHAR(50) NOT NULL,
	`lastname` VARCHAR(50) NOT NULL,
    
    PRIMARY KEY (userid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE INDEX usersEmail ON users (email);

/****************************************************************************/
DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
    `orderid` INT NOT NULL AUTO_INCREMENT,
	`userid` INT NOT NULL,
	`date` DATE NOT NULL,
    `product` VARCHAR(80) NOT NULL,
    `quantity` INT NOT NULL,
    `total` DECIMAL(6,2) NOT NULL,
    `paymentmethod` VARCHAR(15) NOT NULL,
    `transaction` VARCHAR(50) NOT NULL,
    `status` VARCHAR(25) NOT NULL,
    
    PRIMARY KEY (orderid),
    FOREIGN KEY (userid) REFERENCES users(userid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/****************************************************************************/
DROP TABLE IF EXISTS `tickets`;

CREATE TABLE `tickets` (
    `ticketid` INT NOT NULL AUTO_INCREMENT,
	`userid` INT NOT NULL,
	`date` DATE NOT NULL,
    `title` VARCHAR(35) NOT NULL,
	`msgs` TEXT NOT NULL,
    `status` VARCHAR(25) NOT NULL,
    
    PRIMARY KEY (ticketid),
    FOREIGN KEY (userid) REFERENCES users(userid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/****************************************************************************/
DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
    `productid` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `imglink` VARCHAR(25) NOT NULL,
    `price` DECIMAL(6,2) NOT NULL,
    
    PRIMARY KEY (productid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;