name "sown_server"
description "A SOWN server"
run_list([
  "recipe[sown::uk_apt_mirrors]"
])