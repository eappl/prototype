var time_now_server,time_now_client,time_server_client,timerID;
time_end=time_end.getTime();
time_now_server=new Date();//开始的时间
time_now_server=time_now_server.getTime();
time_now_client=new Date();
time_now_client=time_now_client.getTime();
time_server_client=time_now_server-time_now_client;
setTimeout("show_time()",1000);
function show_time()
{
 var timer = document.getElementById("countdown_time_show");
 if(!timer){
 return ;
 }
 timer.innerHTML =time_server_client;
 var time_now,time_distance,str_time;
 var int_day,int_hour,int_minute,int_second;
 var time_now=new Date();
 time_now=time_now.getTime()+time_server_client;
 time_distance=time_end-time_now;
 if(time_distance>0)
 {
  int_day=Math.floor(time_distance/86400000)
  time_distance-=int_day*86400000;
  int_hour=Math.floor(time_distance/3600000)
  time_distance-=int_hour*3600000;
  int_minute=Math.floor(time_distance/60000)
  time_distance-=int_minute*60000;
  int_second=Math.floor(time_distance/1000)
  if(int_hour<10)
   int_hour="0"+int_hour;
  if(int_minute<10)
   int_minute="0"+int_minute;
  if(int_second<10)
   int_second="0"+int_second;
  if(int_day>0)
	  str_time="<font color='#666'>预计将在&nbsp;&nbsp;<font color='red'>"+(int_day*24+int_hour)+"</font>小时<font color='red'>"+int_minute+"</font>分钟<font color='red'>"+int_second+"</font>秒&nbsp;&nbsp;内处理完成。</font>";
  else
	  str_time="<font color='#666'>预计将在&nbsp;&nbsp;<font color='red'>"+int_hour+"</font>小时<font color='red'>"+int_minute+"</font>分钟<font color='red'>"+int_second+"</font>秒&nbsp;&nbsp;内处理完成。</font>";
 
  timer.innerHTML=str_time;
  setTimeout("show_time()",1000);
 }
 else
 {
  timer.innerHTML ="<font color='#666'>很抱歉，本次投诉的处理已超时，我们将尽快为您处理。</font>";
  clearTimeout(timerID)
 }
}