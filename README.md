# CIF Software Development Kit for PHP

The CIF Software Development Kit (SDK) for PHP contains library code and examples designed to enable developers to build applications using CIF.

# Installation
## Debian/Ubuntu
  ```bash
  sudo apt-get install -y php5-cli php5-curl php5-json
  git clone https://github.com/csirtgadgets/php-cif-sdk.git -b master
  cd php-cif-sdk
  sudo cp lib/* /usr/share/php/
  sudo cp bin/cif /usr/local/bin/
  ```
## RedHat, Fedora, etc.
  ```bash
  sudo yum install -y php-cli
  git clone https://github.com/csirtgadgets/php-cif-sdk.git -b master
  sudo cp lib/* /usr/share/php/
  sudo cp bin/cif /usr/local/bin/
  ```
  
# Examples
## Client

This assumes you have a valid .cif config file in your home directory or the current working directory.

  ```bash
  $ cif -q example.com
  ```
  
## API
### Search
  ```php
  function __autoload($class_name) { @include $class_name . '.php'; }

  $cif = new CIFClient('https://localhost/api', '1234');
  print_r($cif->query('example.com', 'medium', 50));
  ```
### Ping
  ```php
  function __autoload($class_name) { @include $class_name . '.php'; }

  $cif = new CIFClient('https://localhost/api', '1234');
  print 'Ping complete in ' . $cif->ping() . "\n";
  ```

# Support and Documentation

You can also look for information at the [GitHub repo](https://github.com/csirtgadgets/php-cif-sdk).

# License and Copyright

Copyright (C) 2014 [Emory University](http://www.emory.edu/)

Free use of this software is granted under the terms of the [GNU Lesser General Public License](https://www.gnu.org/licenses/lgpl.html) (LGPL v3.0). For details see the file ``LICENSE`` included with the distribution.
