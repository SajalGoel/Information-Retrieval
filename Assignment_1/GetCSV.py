import time
import bs4
from bs4 import BeautifulSoup
from time import sleep
import requests
import json
import csv
from random import randint
from html.parser import HTMLParser

def spearman_coeff(rank):
    sum_di2 = 0
    n=len(rank)
    if n==1 and rank[0][0]==rank[0][1]:
        return 1
    elif n==1:
        return 0

    for i in rank:
        sum_di2= sum_di2 + (i[0]-i[1])**2
    print(sum_di2)
    try:
        return round((1-(6*sum_di2)/((n)*(n**2 -1))),2)
    except:
        return 0


def compare(output,google_output):

## PREPROCESS BOTH OUTPUTS - remove http/https, remove last '/' if present, convert url to lower, REMOVE WWW.
    for key,val in output.items():
        for each in range(len(val)):
            val[each] = val[each].replace("www.","")
            val[each] = val[each].lower()
            if 'https' in val[each]:
                val[each] = val[each][5:]
            elif 'http' in val[each]:
                val[each] = val[each][4:]

            if val[each][-1]=='/':
                val[each] = val[each][:-1]

    for key,val in google_output.items():
        if len(key) == 0:
            continue
        for each in range(len(val)):
            val[each] = val[each].replace("www.","")
            val[each] = val[each].lower()
            if 'https' in val[each]:
                val[each] = val[each][5:]
            elif 'http' in val[each]:
                val[each] = val[each][4:]

            if val[each][-1]=='/':
                val[each] = val[each][:-1]

## SAVE SUMS OF ALL IN S1, S2, S3 TO CALCULATE AVG
## FOR EVERY OUTPUT LINK WE GOT - CHECK IF PRESENT IN GOOGLE_OUTPUT & ADD RANKS OF BOTH IN 'RANK'
    csv_rowlist = []
    csv_rowlist.append(["Queries", " Number of Overlapping Results", " Percent Overlap", " Spearman Coefficient"])
    query_count = 1
    s1=0
    s2=0
    s3=0
    for key,val in output.items():
        if len(key) == 0:
            continue
        counter = 0
        rank = []
        for each in val:
            if each in google_output[key]:
                counter+=1
                rank.append((google_output[key].index(each), output[key].index(each)))
        s1+=counter
        s2+=round((counter*100)/len(google_output[key]),1)
        s3+=spearman_coeff(rank)
        csv_rowlist.append(["Query "+str(query_count)," "+str(counter)," "+str(round((counter*100)/len(google_output[key]),1))," "+str(spearman_coeff(rank))])
        query_count+=1

## WRITE TO HW1.CSV
    with open('hw1_TEST.csv', 'w') as file:
        writer = csv.writer(file)
        writer.writerows(csv_rowlist)
        writer.writerows([["Averages ",str(s1/100)," "+str(s2/100)," "+str(s3/100)]])


if __name__ == "__main__":
    
    file = open('Google_Result.json', "r")
    google_output = json.loads(file.read())
    file = open('hw1.json', "r")
    output = json.loads(file.read())
    compare(output,google_output)


