# TeleBot HR
 Telegram bot designed for HR managers. Asks the user to complete the questionnaire, then sends the data to the company's email address or specifically to HR

# How to Use
1. Insert your data from the Database and your API key that BotFather gave you in line 6 and 7 of the file index.php
2. Upload files to your hosting (__`HTTPS` encryption is required__)
3. Set webhook
4. Write `/start` to your bot

# Database

The database is very simple. It must have the required chatid and state parameters. 
_For ex_

| id (a/i) | chatid | state (0-7) |
|----------------|:---------:|----------------:|
| 1 | 22244567 | 0 |
| 2 | 74562324| 6 |
| 3 | 45687632| 3 |
