<?php
/**
 * 秒杀
 * @author zhanglei <zhanglei19881228@sina.com>
 * @date 2018-03-07 15:00
 */
class Timelimited{
    
    private $_redis                     = null;
    
    private $_ip                        = '127.0.0.1';
    
    private $_port                      = 6379;
    
    // 存商品的redis队列的key
    private $_shop_goods_list_key       = 'github_shop_goods';
        
    private $_shop_goods_list_key_bak   = 'github_shop_goods_bak';
    
    private $_hash_key                  = 'github_shop_goods_hash';
    
    public function __construct(){
        $this->_redis = new redis;
        $this->_redis->connect($this->_ip, $this->_port);
    }
    
    // 消费队列, 也就是秒杀进来的入口了
    public function consume_list($user_id){
        if(empty($user_id)) return false;
        
        // redis队列不能保证一致性, 会有丢失的可能, 所以使用rpoplpush, pop的同时往另外的队列里面push一条, 原子性
        $shop_goods_id = $this->_redis->rpoplpush($this->_shop_goods_list_key, $this->_shop_goods_list_key_bak);
        if(empty($shop_goods_id)){
            echo '已经抢光了' . PHP_EOL;
            return false;
        }
        
        // 查看当前用户是不是已经抢到过了, 抢过了不准抢
        $user_shop_goods = $this->_redis->hget($this->_hash_key, $user_id);
        if(!empty($user_shop_goods)){
            echo '你已经抢到了' . PHP_EOL;
            return false;
        }
        
        // 这时候处理当前用户和商品的关系了, 将结果放入到hash中, 方便查看. 你看, 如果高并发来了, 同一个用户都到这里来了, 那这个用户不就抢了多个商品了?
        $watch_key = sprintf('github_watch_%u', $user_id);
        
        $this->_redis->watch($watch_key);
        
        $result = $this->_redis->get($watch_key);
        
        $this->_redis->multi();
        
        $this->_redis->hset($this->_hash_key, $user_id, $shop_goods_id);
        
        $this->_redis->incr($watch_key, empty($result) ? 1 : $result + 1);
        
        $this->_redis->exec();
        
        
        return true;
    }
    
    // 查看哪些人抢到了商品
    public function check(){
        return $this->_redis->hgetall($this->_hash_key);
    }
    
    // 往队列里面写几个商品
    public function push(){
        $num = 10;
        
        for($i = 0; $i < $num; $i++){
            $this->_redis->lpush($this->_shop_goods_list_key, rand(10000, 99999));
        }
        
        return true;
    }
    
    // 清空数据库
    public function flush(){
        $this->_redis->flushAll();
    }
    
}

$model = new Timelimited();
    
if($argv[1] == 'check'){
    
    // 查看哪些用户抢到了
    $result = $model->check();
    foreach($result as $user_id => $shop_goods_id){
        echo sprintf('用户id: %u, 商品id: %u', $user_id, $shop_goods_id) . PHP_EOL;
    }
    
}elseif($argv[1] == 'push'){
    
    // 往里面push数据
    $model->push();
    
}elseif($argv[1] == 'flush'){
    
    // 清空数据库, 方便观察
    $model->flush();
    
}else{

    // user_id为10 - 30个用户, 100个并发(100个并发进程, 总共访问1000次, 详细看readme http_load), 重复率应该比较高了, 看看是不是有用户抢到多个商品
    $user_id = rand(10, 30);

    $model->consume_list($user_id);

}