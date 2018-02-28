<p align="center">
    <h1 align="center">Commit History App Yii1</h1>
    <br>
</p>

## Installing using Vagrant

This way is the easiest but long (~20 min).

**This installation way doesn't require pre-installed software (such as web-server, PHP, MySQL etc.)** - just do next steps!

#### Manual for Linux/Unix users

1. Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
2. Install [Vagrant](https://www.vagrantup.com/downloads.html)
3. Prepare project:
   
   ```bash
   git clone https://github.com/lyubch/commit-history.git
   cd commit-history/vagrant/config
   cp vagrant-local.example.yml vagrant-local.yml
   ```
   
4. Edit settings at your `vagrant-local.yml` if need
5. Change directory to project root:

   ```bash
   cd commit-history
   ```

5. Run command:

   ```bash
   vagrant up
   ```
   
That's all. You just need to wait for completion! After that you can access project locally by URLs:
* Application URL: http://commit-history.local
* PhpMyAdmin URL: http://commit-history.local/phpmyadmin
   
#### Manual for Windows users

1. Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
2. Install [Vagrant](https://www.vagrantup.com/downloads.html)
3. Reboot
4. Prepare project:
   * download repo [phalcon-app](https://github.com/lyubch/commit-history/archive/master.zip)
   * unzip it
   * go into directory `commit-history-master/vagrant/config`
   * copy `vagrant-local.example.yml` to `vagrant-local.yml`

5. Edit settings at your `vagrant-local.yml` if need

6. Open terminal (`cmd.exe`), **change directory to project root** and run command:

   ```bash
   vagrant up
   ```
   
   (You can read [here](http://www.wikihow.com/Change-Directories-in-Command-Prompt) how to change directories in command prompt) 

That's all. You just need to wait for completion! After that you can access project locally by URLs:
* Application URL: http://commit-history.local
* PhpMyAdmin URL: http://commit-history.local/phpmyadmin
