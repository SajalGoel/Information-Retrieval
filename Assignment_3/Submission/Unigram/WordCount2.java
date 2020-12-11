import java.io.IOException;
import java.util.HashMap;
import java.util.StringTokenizer;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class WordCount2 {

	public static class WordCountMapper extends Mapper<LongWritable, Text, Text, Text>
    {
            private Text word = new Text();
            @Override
            public void map(LongWritable key, Text value, Context context) throws IOException, InterruptedException
            {


             String line = value.toString().toLowerCase();

String keys[] = line.split("\t",2);
Text doc = new Text(keys[0]);

keys[1] = keys[1].replaceAll("[^a-z ]+", " ");
//for(int i=0; i< arr.length; i++){
//     arr[i] = arr[i].replaceAll("[^a-z ]+", " ");
//}

               //Text outputValue = new Text(arr[0]);
               StringTokenizer tokenizer = new StringTokenizer(keys[1]);
               while(tokenizer.hasMoreTokens())
               {
                word.set(tokenizer.nextToken());
                context.write(word, doc);
               }
            }
    }
    public static class WordCountReducer extends Reducer<Text, Text, Text, Text>
    {
            @Override
            public void reduce(Text key, Iterable<Text> values, Context context) throws IOException, InterruptedException
            {

                     HashMap<String, Integer> currentcounter = new HashMap();
                     for(Text currentval: values)
                     {
                            String value = currentval.toString();
                            if(currentcounter.containsKey(value))
                             {

                                    int curcount = currentcounter.get(value);
                                    curcount = curcount + 1;
                                     Integer a1 = new Integer(curcount);
                                    currentcounter.put(value, a1);
                             }else{

                                     currentcounter.put(value,  new Integer(1));
                                     }
                     }
                     StringBuilder stringbuild = new StringBuilder("");
                     for(String curstr: currentcounter.keySet())
                     {
                             stringbuild.append(curstr+":"+currentcounter.get(curstr)+" ");
                     }
                     Text outputValue = new Text(stringbuild.toString());
                     context.write(key, outputValue);
            }
    }
    public static void main(String args[]) throws IOException, InterruptedException, ClassNotFoundException
    {
            if(args.length != 2)
            {
                    System.out.println("Not enough");
            }else{
                    Configuration c = new Configuration();
                    Job j = Job.getInstance(c, "word count");
                    j.setJarByClass(WordCount2.class);
                    j.setMapperClass(WordCountMapper.class);
                    j.setReducerClass(WordCountReducer.class);
                    j.setMapOutputKeyClass(Text.class);
                    j.setMapOutputValueClass(Text.class);
                    j.setOutputKeyClass(Text.class);
                    j.setOutputValueClass(Text.class);
                    FileInputFormat.addInputPath(j, new Path(args[0]));
                    FileOutputFormat.setOutputPath(j, new Path(args[1]));
                    System.exit(j.waitForCompletion(true)? 0 : 1);
            }
    }

}
