# EmptyRooms
Web scraper returning all available computer science labs at FCUL.

## Description
This project includes a Python scraper, SQL scripts to setup the database and a ready-to-be-deployed PHP frontend.
The Python script parses shedules available online to determine which rooms are empty.
The SQL scripts were written for MySQL 5.6.
This project does not use any JavaScript.
Efficiency was of extreme importance, so payloads were kept to a minimum size and all scripts are memory ant time efficient.

To be able to access your database, either add your credentials to the PHP/Python files or add them to the .config-private files (after renaming from .config-private_sample).
