/*****获取验证码****/
var clock = '';
 var nums = 60;
 var btn;
 function sendCode(thisBtn)
 { 
 btn = thisBtn;
// btn.style = true; //将按钮置为不可点击
document.getElementById('btn').style.backgroundColor='#fff';
document.getElementById('btn').style.color='#ccc';
document.getElementById('btn').style.borderColor='#ccc';
 btn.text = nums+'秒后重发';
 clock = setInterval(doLoop, 1000); //一秒执行一次
 }
 function doLoop()
 {
 nums--;
 if(nums > 0){
  btn.text = nums+'秒后可重发';
 }else{
  clearInterval(clock); //清除js定时器
//btn.disabled = false;
document.getElementById('btn').style.backgroundColor='#fff';
  btn.text = '获取验证码';
  nums = 60; //重置时间
 }
 }