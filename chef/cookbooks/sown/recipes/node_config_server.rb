# Some dependencies for the setup below
include_recipe 'database'
include_recipe 'mysql::client'
package 'build-essential'
gem_package 'mysql'

db_conn = {
  :host => "localhost",
  :username => 'root',
  :password => node['mysql']['server_root_password']
}

mysql_database 'sown_data' do
  connection db_conn
  action :create
end

mysql_database_user 'sown' do
  connection db_conn
  password 'password'
  database_name 'sown_data'
  privileges [:all]
  action :grant
end

execute 'create_sown_schema' do
  command 'mysql -u sown --password=password -e "source /vagrant/node_control/sql/sown_data.sql;" sown_data'
  not_if '[ `mysql -B -u sown --password=password -e "show tables;" sown_data | wc -l` -gt 0 ]'
end

apt_repository "kohana" do
  uri "http://ppa.launchpad.net/kohana/stable/ubuntu"
  distribution node['lsb']['codename']
  components ["main"]
  keyserver "keyserver.ubuntu.com"
  key "F6D2C94B"
end

pkgs = %w{ libkohana3.2-core-php libkohana3.2-mod-auth-php libkohana3.2-mod-cache-php libkohana3.2-mod-codebench-php libkohana3.2-mod-database-php libkohana3.2-mod-image-php libkohana3.2-mod-orm-php libkohana3.2-mod-unittest-php }
pkgs.each do |pkg|
  package pkg
end

package 'git'

directory '/srv/www/' do
  recursive true
end

if File.directory? '/vagrant/node_control'
  link '/srv/www/default' do
    to '/vagrant/node_control'
  end
else
  git "/srv/www/default" do
    repository "git://github.com/sown/node_control.git"
  end
end

apache_site 'default' do
  enable false
end

web_app 'node_control' do
  server_name node['hostname']
  server_aliases [node['fqdn'], "localhost"]
  docroot '/srv/www/default'
end

# TODO Hack httpd to use /srv/www/default
# TODO Hack httpd.conf to allow allow override all
# TODO install sown vhost

git "/srv/www/KODoctrine2" do
  repository "https://github.com/Flynsarmy/KODoctrine2.git"
end
link "/usr/share/php/kohana3.2/modules/doctrine2" do
  to "/srv/www/KODoctrine2/modules/doctrine2/"
end

git "/srv/www/PHP-IPAddress" do
  repository "https://github.com/leth/PHP-IPAddress.git"
  reference '3.2/master'
end
link "/usr/share/php/kohana3.2/modules/php-ipaddress" do
  to "/srv/www/PHP-IPAddress/"
end

remote_file "/tmp/DoctrineORM-2.2.2-full.tar.gz" do
  source "http://www.doctrine-project.org/downloads/DoctrineORM-2.2.2-full.tar.gz"
  # checksum "xxx" # A SHA256 (or portion thereof) of the file.
end

script 'install_doctrine_vendor_code' do
  interpreter "bash"
  cwd '/tmp'
  code <<-EOH
    tar -zxf DoctrineORM-2.2.2-full.tar.gz
    mv DoctrineORM-2.2.2/* /usr/share/php/kohana3.2/modules/doctrine2/classes/vendor/doctrine
    rm DoctrineORM-2.2.2-full.tar.gz
    rmdir DoctrineORM-2.2.2
  EOH

  not_if '[ `ls -1 /usr/share/php/kohana3.2/modules/doctrine2/classes/vendor/doctrine | wc -l` -gt 1 ]'
end

# There's not a nice way to do chmods it seems.
script 'webserver_writeable_folder_permissions' do
  interpreter "bash"
  cwd '/tmp'
  code <<-EOH
    chmod 2777 /srv/www/default/application/logs
    chmod 2777 /srv/www/default/application/cache
    chmod 2777 -R /srv/www/default/application/models/proxies
    chown -R root:www-data /srv/www/default/application
  EOH
end
