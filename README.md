# PiSimpleSignage
Simple, lean digital signage tool for managing &amp; displaying images on multiple signage screens.
Management is done in an web interface. To display the images, just run a browser in Kiosk mode and point it to the correct URL!

# Disclaimer
I have been using this tool in its current form for a couple of months now without any issues. That said, it is still not production-level software. Use it at your own risks!

## Getting Started

There is no automated way to set it up until now. However, it is fairly straightforward.

**If you know what you are doing**, here the tl;dr: Copy the content of `html` to your webserver. Make any changes neccessary to `config/config.php`. Make sure to set proper access rights, refer to the `.htaccess` files in `html` and its subfolders for the recommended way.\
If you want to use any other database than sql, in `sql_create.txt`, the commands to create the tables and triggers neccessary are shown. Furthermore, you will need to change the database connector in the `php` files.

Furthermore, any contributions are welcome! Just send me a pull request if you add anything useful!\
**If you do not know what you are doing** and the (fairly beta) instructions below are not enough, contact me at github@meyerweb.eu. I will try to help the best I can and if I fell that this project gains traction, I will implement an automated way to set it up.

### Prerequisites
You need:
* A RaspberryPi (or any other PC) connected to your display. PiZero works as well. Examples are supplied for Linux.
* Access to a web server (.htaccess files are supplied for Apache 2.4). Can also run directly on the RPi.

### Setup
* Copy the content of the `html` folder to your webserver. If you are not using Apache2.4 or higher, be sure to adjust the `.htaccess` files.
  * For displaying images, the client needs acces to `ajax.php`, `display.php`, `images/`, `js` and `css`.
  * The rest of the files in `html` only needs to be accessed for managing the images and devices.
  * No one needs access to `config/` via the webserver. The `config.php` is read via an include statement in `php` and never by `GET`.

* The easiest way to protect the management interface is to set up `BasicAuth` with your webserver. An example for Apache can be found here: https://www.digitalocean.com/community/tutorials/how-to-set-up-password-authentication-with-apache-on-ubuntu-14-04 If you but the `.htpasswd` file anywhere else than `/etc/apache2/.htpasswd`, adjust the path in `html/.htaccess/` accordingly.
* If you run `PiSimpleSignage` in a subfolder (e.g. `https://your-domain.com/<your-folder>/`), add the subfolder in `html/.htaccess/` like this:
```
AuthType Basic
AuthName "PiSimpleSignage"
AuthUserFile /etc/apache2/.htpasswd

<RequireAny>
	Require expr %{REQUEST_URI} = "/<your-folder>/display.php"
	Require expr %{REQUEST_URI} = "/<your-folder>/ajax.php"
	Require valid-user
</RequireAny>
```

You should be good to go! Just open go to `index.php` in your browser for the server and start adding images. The url for displaying the images is shown on the devices page for each device.
