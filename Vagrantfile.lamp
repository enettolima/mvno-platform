Vagrant.configure("2") do |config|
	config.vm.box = "osm_precise64_lamp"
  config.vm.box_url = "http://dev.opensourcemind.us/boxes/osm_precise64_lamp.box"
	config.vm.network "public_network"
  config.vm.network "forwarded_port", guest: 80, host: 8888
  config.vm.network "forwarded_port", guest: 3306, host: 33060
	config.vm.synced_folder ".", "/var/www", owner: "www-data", group: "www-data"
end
