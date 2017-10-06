# 159.339 A2: PHP MVC Banking System

Gwatkin, 15146508

## Specifications

Simple internet banking system.

Simulates account creation and allows transfers of money between accounts.

Created to display an understanding of the MVC framework in web development.

## Design choices

The overall design was kept simple to ensure use of MVC framework is obvious. Both user account and transfer models 
are used so that relational mapping can be demonstrated.

Admin user is able to manage accounts in the system through the view they are greeted with after logging in. This 
allows new accounts to be created and old accounts to be deleted.

Other users are able to make transfers from their account to other accounts.

## Database schema and relations

* user_account
    * id - account ID
    * username - user name
    * password - user password
    * balance - user account balance

* transfer
    * id - Transfer ID
    * dateTimeOf - datetime of transfer
    * valueOf - value transferred
    * fromAccount - ID of account from which value was transferred
    * toAccount - ID of account to which value was transferred

## Installation instructions

### Requirements

Docker Toolbox

### Instructions

1. Download all files
1. Use "Docker Quickstart Terminal" in root folder (containing `docker-compose.yml` file) to install and run server
    1. Enter command `docker-compose up`
    1. Wait for packages to be installed and containers to run
1. For web-app use
    1. Navigate to `192.168.99.100:8000`
    1. Log in with username 'admin' and password 'admin'
    1. Default account passwords are the account name in lowercase
1. For database view
    1. Navigate to `192.168.99.100:8888`

## Instructions for end-user

### Admin user

Admin user logs in with username 'admin' and password 'admin'

Once logged in, can view a list of user accounts in the system and their current balance.

Can create new accounts or delete existing accounts.

Can log out at any time.

### Other user

Must have account created by admin user. Use given username and password to login.

Once logged in, can view a list of transfers made on their account.

Can make a new transfer to another account in the system.
