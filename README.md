# Klump Payment Gateway

### How to deploy to WordPress
[Wordpress](https://wordpress.org/) uses [SVN](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/) for version management.
Ensure you have SVN installed on your system.
- `svn up` to get the latest version of the code (Note: you'll be required to provide username and password for WordPress account)
- Make changes to the `trunk` folder and run `svn stat` to see changes made
- `svn diff` shows exact changes in code
- `svn add .` will add all the changes to be committed.
- `svn ci -m "{commit message here}"` to deploy changes.
- `svn cp trunk tags/{version number}` to tag a new version specifying the version number. See tags folder and increment number accordingly. Remember to add specify the stable tag in readme.txt file.
- `svn add .`
- `svn ci -m "tagging version {version number}"`
- Congrats. Code deployed and available for users to update on their installation.

### How to test
- Clone repo in `wp-content/plugins` folder of a wordpress installation. You can set up installation with [localwp](https://localwp.com/).
- If you don't have production credentials, add `staging-` prefix to `KLP_WC_SDK_URL` and `KLP_WC_SDK_VERIFICATION_URL` in `klump-wc-payment.php` file to test with staging credentials.
- Login as admin and activate plugin (note: plugin requires woocommerce installed and setup)
- Add Klump credentials and activate it.
- Checkout product and select Klump as payment gateway.

### Authors

- Celestine Omin
- Temitope
- [Imo-owo Nabuk](https://github.com/richienabuk)
