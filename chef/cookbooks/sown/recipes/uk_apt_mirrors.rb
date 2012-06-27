include_recipe "apt"

execute "download_new_mirror_index" do
  command "apt-get update"
  action :nothing
end

execute "Change to ja.net based mirror" do
  command "sed --in-place=.orig 's/us.archive.ubuntu.com/ubuntu-archive.mirrorservice.org/' /etc/apt/sources.list"
  # v- Only run this once, when the file doesn't exist
  creates "/etc/apt/sources.list.orig"
  # Run apt-get update after this command has been run
  notifies :run, "execute[download_new_mirror_index]", :immediately
end
