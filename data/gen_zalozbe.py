#!/usr/bin/python3

import re
# python3-mysqldb paket
import MySQLdb

baza_old = MySQLdb.connect(
    host="localhost",
    user="fonoteka",
    passwd="fonoteka",
    database="fonoteka_old",
    charset="utf8",
    use_unicode=True
)

kazalec = baza_old.cursor()

albumi = {}
zalozbe = {}

kazalec.execute("SELECT STEVILKA, ZALOZBA FROM FONO_ALL WHERE ZALOZBA <> ''")

rezultat = kazalec.fetchall()

# zacetni ID za zalozbe
jid = 1

for r in rezultat:
    album_fid = r[0].split('-')[0]

    zalozba = r[1].replace('È', 'Č') # Encoding fix

    album_split = album_fid.split(' ')
    index = album_fid

    if zalozba is not '':

        if index not in albumi:
            albumi[index] = set()

        albumi[index].add(zalozba)

        if zalozba not in zalozbe:
            zalozbe[zalozba] = jid
            jid += 1

# albumi z zalozbo
#print(albumi)
#print()

# Vse zalozbe
#print(zalozbe)
#print(len(zalozbe))


# Izgradimo sql za vnos zalozb
for j, jid in zalozbe.items():
    print('INSERT INTO data_label (id, name) VALUES (', jid, ", '" + j.replace("'", "\\'") + "');")

# Izgradimo sql asociacijo  z albumi

baza_new = MySQLdb.connect(
    host="127.0.0.1",
    port=33060,
    user="FONOTEKA",
    passwd="huehuehue",
    database="fonoteka_pica",
    charset="utf8",
    use_unicode=True
)

kazalec = baza_new.cursor()
for fid, data in albumi.items():
    #print(fid)
    kazalec.execute("SELECT ID FROM data_albums WHERE FID='" + fid + "'")

    # TODO preveri konflikt z obstojece vpisanim?

    album_zalozba = next(iter(data))

    rezultat = kazalec.fetchall()
    for r in rezultat:
        zalozba_id = zalozbe[album_zalozba]

        print("INSERT INTO rel_album2label (album_id, label_id) VALUES (" + str(r[0]) + ',' + str(zalozba_id) + ");")

