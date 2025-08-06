CREATE DATABASE IF NOT EXISTS RailwayManagement;
USE RailwayManagement;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);
select * from users;
CREATE TABLE IF NOT EXISTS post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    designation VARCHAR(20) NOT NULL UNIQUE -- Added UNIQUE constraint
);
CREATE TABLE IF NOT EXISTS department (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(50) NOT NULL UNIQUE -- Added UNIQUE constraint
);
CREATE TABLE IF NOT EXISTS DESTINATION (
    placeName VARCHAR(50) PRIMARY KEY,
    location VARCHAR(50) NOT NULL
);
CREATE TABLE IF NOT EXISTS trains (
    train_id VARCHAR(50) NOT NULL UNIQUE PRIMARY KEY,
    train_name VARCHAR(100) NOT NULL UNIQUE, -- Added UNIQUE constraint
    ac_seats INT NOT NULL,
    non_ac_seats INT NOT NULL
);
CREATE TABLE IF NOT EXISTS TrainSchedule (
    TrainNumber VARCHAR(50) PRIMARY KEY,
    FromStation VARCHAR(100) NOT NULL,
    ToStation VARCHAR(100) NOT NULL,
    DepartureTime TIME NOT NULL,
    ArrivalTime TIME NOT NULL,
    Status VARCHAR(50) NOT NULL,
    Contact VARCHAR(100) NOT NULL,
    ACSeats INT NOT NULL, -- Added column for AC seats
    NonACSeats INT NOT NULL, -- Added column for Non-AC seats
    Price DECIMAL(10, 2) NOT NULL, -- Added column for ticket price
    Date DATE NOT NULL, -- Added column for date
    train_name VARCHAR(100) NOT NULL, -- Added train_name column
    FOREIGN KEY (TrainNumber) REFERENCES trains(train_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (FromStation) REFERENCES DESTINATION(placeName) ON DELETE CASCADE ON UPDATE CASCADE, -- Updated foreign key
    FOREIGN KEY (ToStation) REFERENCES DESTINATION(placeName) ON DELETE CASCADE ON UPDATE CASCADE, -- Updated foreign key
    FOREIGN KEY (train_name) REFERENCES trains(train_name) ON DELETE CASCADE ON UPDATE CASCADE, -- Added foreign key
    INDEX (ACSeats), -- Added index for ACSeats
    INDEX (NonACSeats) -- Added index for NonACSeats
);
CREATE TABLE IF NOT EXISTS Employee (
    EmployeeID VARCHAR(10) PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Designation VARCHAR(20) NOT NULL, -- Changed to VARCHAR
    Department VARCHAR(50) NOT NULL, -- Changed to VARCHAR
    Contact VARCHAR(15) NOT NULL,
    Salary DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (Designation) REFERENCES post(designation) ON DELETE CASCADE ON UPDATE CASCADE, -- Updated foreign key
    FOREIGN KEY (Department) REFERENCES department(department) ON DELETE CASCADE ON UPDATE CASCADE -- Updated foreign key
);
CREATE TABLE IF NOT EXISTS Passenger (
    PassengerID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Age INT NOT NULL,
    Gender VARCHAR(10) NOT NULL,
    Contact VARCHAR(15) NOT NULL,
    RegistrationDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS TicketPricing (
    TrainNumber VARCHAR(50) NOT NULL,
    ClassType VARCHAR(50) NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (TrainNumber, ClassType),
    FOREIGN KEY (TrainNumber) REFERENCES trains(train_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Booking (
    BookingID INT PRIMARY KEY AUTO_INCREMENT,
    PassengerID INT NOT NULL,
    TrainNumber VARCHAR(50) NOT NULL, -- Changed INT to VARCHAR(50) to match TicketPricing
    ClassType VARCHAR(50) NOT NULL,
    BookingDate DATETIME NOT NULL, -- Changed from DATE to DATETIME
    booked_seat INT NOT NULL, -- Replaced ac_seats and non_ac_seats with booked_seat
    FOREIGN KEY (PassengerID) REFERENCES Passenger(PassengerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (TrainNumber, ClassType) REFERENCES TicketPricing(TrainNumber, ClassType) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insert sample data into users table
INSERT INTO users (email, password) VALUES 
('user1@example.com', 'password1'),
('user2@example.com', 'password2');

-- Insert sample data into profiles table
INSERT INTO profiles (Name, Age, Gender, Contact) VALUES 
('John Doe', 30, 'Male', '5555555555'),
('Jane Smith', 25, 'Female', '6666666666');

-- Insert sample data into post table
INSERT INTO post (designation) VALUES 
('Manager'),
('Clerk');

-- Insert sample data into department table
INSERT INTO department (department) VALUES 
('HR'),
('Operations');

-- Insert sample data into DESTINATION table
INSERT INTO DESTINATION (placeName, location) VALUES 
('StationA', 'CityA'),
('StationB', 'CityB'),
('StationC', 'CityC'),
('StationD', 'CityD'), -- Added more sample data
('StationE', 'CityE'); -- Added more sample data

-- Insert sample data into trains table
INSERT INTO trains (train_id, train_name, ac_seats, non_ac_seats) VALUES 
('T001', 'Express1', 50, 100),
('T002', 'Express2', 60, 120),
('T003', 'Express3', 70, 140); -- Added a unique train_id

-- Insert sample data into TrainSchedule table
INSERT INTO TrainSchedule (TrainNumber, FromStation, ToStation, DepartureTime, ArrivalTime, Status, Contact, ACSeats, NonACSeats, Price, Date, train_name) VALUES 
('T001', 'StationA', 'StationB', '08:00:00', '12:00:00', 'On Time', '1234567890', 50, 100, 500.00, '2023-10-01', 'Express1'),
('T002', 'StationB', 'StationA', '14:00:00', '18:00:00', 'Delayed', '0987654321', 60, 120, 600.00, '2023-10-02', 'Express2'),
('T003', 'StationA', 'StationC', '10:00:00', '14:00:00', 'On Time', '1122334455', 70, 140, 700.00, '2023-10-03', 'Express3'); -- Ensure sample data matches the query criteria

-- Insert sample data into Employee table
INSERT INTO Employee (EmployeeID, Name, Designation, Department, Contact, Salary) VALUES 
('E001', 'Alice', 'Manager', 'HR', '1111111111', 75000.00), -- Changed to use VARCHAR values
('E002', 'Bob', 'Clerk', 'Operations', '2222222222', 35000.00); -- Changed to use VARCHAR values

-- Insert sample data into Passenger table
INSERT INTO Passenger (Name, Age, Gender, Contact) VALUES 
('Charlie', 30, 'Male', '3333333333'),
('Diana', 25, 'Female', '4444444444');

-- Insert sample data into TicketPricing table
INSERT INTO TicketPricing (TrainNumber, ClassType, Price) VALUES 
('T001', 'AC', 500.00),
('T001', 'Non-AC', 300.00),
('T002', 'AC', 600.00),
('T002', 'Non-AC', 400.00);

-- Insert sample data into Booking table
INSERT INTO Booking (PassengerID, TrainNumber, ClassType, BookingDate, booked_seat) VALUES 
(1, 'T001', 'AC', '2023-10-01 09:00:00', 1), -- Updated to include booked_seat
(2, 'T002', 'Non-AC', '2023-10-02 15:00:00', 2); -- Updated to include booked_seat

DROP TABLE IF EXISTS users; -- Then drop users table
DROP TABLE IF EXISTS Booking;
DROP TABLE IF EXISTS TicketPricing;
DROP TABLE IF EXISTS TrainSchedule;
DROP TABLE IF EXISTS Passenger;
DROP TABLE IF EXISTS Employee;
DROP TABLE IF EXISTS DESTINATION;
DROP TABLE IF EXISTS department;
DROP TABLE IF EXISTS trains;
DROP TABLE IF EXISTS post;