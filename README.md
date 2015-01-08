picapica
========
![pica pica](https://upload.wikimedia.org/wikipedia/commons/b/b6/Pica_pica_-_Compans_Caffarelli_-_2012-03-16.jpg)

Web app for physical music library management.

## Front end dependency installation

Once your vagrant box is up and provisioned, run the following commands:
```bash
npm install --no-bin-links
bundle install
grunt
```
If your host machine is not Windows based, you can drop the `--no-bin-links` on the first line.
If the `grunt` command isn't working for you, try running `sudo npm install -g grunt-cli` first.