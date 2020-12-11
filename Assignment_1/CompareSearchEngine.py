from bs4 import BeautifulSoup
from time import sleep
import requests
from random import randint
from html.parser import HTMLParser
import json

USER_AGENT = {'User-Agent':'Mozilla/5.0 (Windows NT 10.0; Win64; x64)\
              AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36'}

SEARCHING_URL = 'http://www.bing.com/search?q='
SEARCH_SELECTOR = 'li' #["li", attrs = {"class" : "b_algo"}]
              
class SearchEngine:
    @staticmethod
    def search(query, sleepify=True):
        if sleepify: # Prevents loading too many pages too soon
            sleep(randint(10, 100))
        temp_url = '+'.join(query.split()) #for adding + between words for the query
        
        url = SEARCHING_URL + temp_url + "&count=30"
        soup = BeautifulSoup(requests.get(url, headers=USER_AGENT).text, "html.parser")
        new_results = SearchEngine.scrape_search_result(soup)
        return new_results

    @staticmethod
    def scrape_search_result(soup):
        raw_results = soup.find_all("li", attrs = {"class" : "b_algo"})
        #print (raw_results)
        results = []
        seen = set()
        count=0
        #implement a check to get only 10 results and also check that URLs must not be duplicated
        
        for result in raw_results:
            link = result.find('a').get('href')
            if link not in seen:
                if count<10:
                    seen.add(link)
                    results.append(link)
                    count = count + 1
            #print(link)
            #results.append(link)
        return results

if __name__ == '__main__':

    filepath = '100QueriesSet1.txt'
    #data = {}
    #data['BingResults'] = []
    resDic = {}
    with open(filepath) as fp:
        line = fp.readlines()
        #print(line)
        for l in line:
            res = SearchEngine.search(l.strip())
            resDic[l.strip()] = res;     
        #data['BingResults'].append(resDic)
        
    with open('hw1.txt', 'w') as outfile:
        json.dump(resDic, outfile)

