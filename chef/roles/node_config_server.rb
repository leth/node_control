name "node_config_server"
description "A SOWN Node config server"
run_list([
	"role[sown_server]",
  
  "recipe[mysql::server]",

  "recipe[apache2::mod_rewrite]",
  "recipe[apache2::mod_php5]",
  "recipe[apache2::mod_ssl]",

  "recipe[php::module_mysql]",
  "recipe[php::module_curl]",

  # The complex part is done in here
  "recipe[sown::node_config_server]",
])