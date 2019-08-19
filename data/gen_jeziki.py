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
jeziki = {}

kazalec.execute("SELECT STEVILKA, JEZIK FROM FONO_ALL WHERE JEZIK <> ''")

rezultat = kazalec.fetchall()

# zacetni ID za jezike
jid = 1

for r in rezultat:
    album_fid = r[0].split('-')[0]
    jezik_arr = re.split("\s*[/,]\s*", r[1])

    album_split = album_fid.split(' ')
    index = album_fid

    if index not in albumi:
        albumi[index] = set()

    for j in jezik_arr:
        j = j.replace('È', 'Č') # Encoding fix

        if j is not '':
            albumi[index].add(j) # Encoding fix

            if j not in jeziki:
                jeziki[j] = jid
                jid += 1

# albumi z artisti in jeziki
#print(albumi)
#print()

# Vsi jeziki
#print(jeziki)
#print(len(jeziki))


# Izgradimo sql za vnos jezikov
for j, jid in jeziki.items():
    print('INSERT INTO data_herkunft (id, name) VALUES (', jid, ", '" + j.replace("'", "\\'") + "');")

# Izgradimo sql asociacijo jezikov z albumi

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
for fid, album_jeziki in albumi.items():
    #print(fid)
    kazalec.execute("SELECT ID FROM data_albums WHERE FID='" + fid + "'")

    rezultat = kazalec.fetchall()
    for r in rezultat:
        for j in album_jeziki:
            herkunft_id = jeziki[j]

            print("INSERT INTO rel_album2herkunft (album_id, herkunft_id) VALUES (" + str(r[0]) + ',' + str(herkunft_id) + ");")

