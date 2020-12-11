package ControllerPackage;

//package csci572;

import edu.uci.ics.crawler4j.crawler.CrawlConfig;
import edu.uci.ics.crawler4j.crawler.CrawlController;
import edu.uci.ics.crawler4j.fetcher.PageFetcher;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtConfig;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtServer;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.PrintWriter;
import java.io.File;




public class Controller {

	public static void main(String[] args) throws Exception {
		// TODO Auto-generated method stub
		String crawlStorageFolder = "data";
        int numberOfCrawlers = 7;
        CrawlConfig config = new CrawlConfig();
        config.setCrawlStorageFolder(crawlStorageFolder);

        /* basic configuration */
        config.setMaxDepthOfCrawling(16);
        config.setMaxPagesToFetch(20000);
        config.setPolitenessDelay(203);
        config.setSocketTimeout(30000);
        config.setUserAgentString("Chrome/61.0.3163.100");
        config.setIncludeBinaryContentInCrawling(true);

        /* Instantiate the controller for this crawl. */
        PageFetcher pageFetcher = new PageFetcher(config);
        RobotstxtConfig robotstxtConfig = new RobotstxtConfig();
        RobotstxtServer robotstxtServer = new RobotstxtServer(robotstxtConfig, pageFetcher);
        CrawlController controller = new CrawlController(config, pageFetcher, robotstxtServer);

        /* add seed url */
        controller.addSeed("https://www.nytimes.com/");
        /* Start the crawl */
        controller.start(MyCrawler.class, numberOfCrawlers);
	}

}

