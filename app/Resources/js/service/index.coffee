"use strict"

app = require("angular").module "picapicaApp"

app.service "SearchFilter", require "./search-filter"
app.factory "Track", require "./track"
