# RPUK Police Database

### Setup

To use, you need to edit config-example.php to contain your MYSQL database details.
Then, rename config-example.php to config.php

Run setup.php to generate the database and tables that you need. This file will delete itself as you only need it once.

After this, you should be able to use the website.

### QoL

To have an image on the main login screen, you will need to place a PNG called login.png into the images folder. One is not included for copyright purposes.
To display profile pictures on each user's profile, you need to insert a png with that user's character ID into a Profiles folder inside images. Example: images/Profiles/12345.png