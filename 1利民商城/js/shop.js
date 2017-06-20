

$(function(){
    



  // 购物车加
  $('.add').click(function() {
    var num =change($(this),+1);
    setTotal1();
    if($(this).parents('.list').find('.xuan').hasClass('selected')){
      setTotal()
    }

  })
  // 购物车减
  $('.reduce').click(function() {
    if($(this).siblings('.num-display').text()<=1){
      return
    }
    var num =change($(this),-1);
    setTotal1();
    if($(this).parents('.list').find('.xuan').hasClass('selected')){
      setTotal()
    }
    
  });




  // 购物车数量加减
  function change(o,add){
    var num=parseInt(o.siblings('.num-display').text())+add;
    o.siblings('.num-display').text(num);
    $('#snum').text(num);
    return num
  }

  // 购物车金额计算
  function setTotal(){ 
    var s=0; 
    $(".selected").parents('.list').each(function(){ 
      s+=parseInt($(this).find('.num-display').text())*parseFloat($(this).find('.s-val').text());
    }); 
    $(".settlement .zuo span").html('￥'+s.toFixed(2)); 
  } 

  // 购物车金额计算2
  function setTotal1(){ 
    var s1=0; 

    s1+=parseInt($('.danjia').text())*parseFloat($('.snum').text());
    $("#allnum").html(s1); 
  } 


  // 购物车选中
  $('.xuan').click(function(event) {
    if($(this).hasClass('selected')){
      $(this).removeClass('selected');
      setTotal()
    }else{
      $(this).addClass('selected');
      setTotal()
    }
  });

  // 删除购物车
  $('.icon-lajixiang').click(function(event) {
    var r=confirm("确定要删除该商品吗?")
        if(r){ 
          $(this).parents('.list').remove();
          setTotal()
        }
  });





})





























































































































