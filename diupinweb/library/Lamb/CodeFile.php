<?php
class Lamb_CodeFile
{
 //验证码位数
 private $mCheckCodeNum  = 4;

 //产生的验证码
 private $mCheckCode   = '';
 
 //验证码的图片
 private $mCheckImage  = '';

 //干扰像素
 private $mDisturbColor  = '';

 //验证码的图片宽度
 private $mCheckImageWidth = '80';

 //验证码的图片宽度
 private $mCheckImageHeight  = '20';
 
 //验证码背景颜色的RGB值
 private $mBgRGB = array(
 	'r' => 200,
 	'g' => 200,
 	'b' => 200
 );
 
 public $mSessionVarName = 'randval';

 /**
 *
 * @brief  输出头
 *
 */
 private function OutFileHeader()
 {
  header ("Content-type: image/png");
 header ("Cache-Control: no-cache");
 }

 /**
 *
 * @brief  产生验证码
 *
 */
 private function CreateCheckCode()
 {
  $this->mCheckCode = strtoupper(substr(md5(rand()),0,$this->mCheckCodeNum)); 
  //session_save_path(dirname(__FILE__)."/session");
  @session_start(); 
  $randval=$this->mCheckCode;
  $_SESSION[$this->mSessionVarName]=$this->mCheckCode;
  return $this->mCheckCode;
 }

 /**
 *
 * @brief  产生验证码图片
 *
 */
 private function CreateImage()
 {
  $this->mCheckImage =@imagecreate ($this->mCheckImageWidth,$this->mCheckImageHeight);
  imagecolorallocate ($this->mCheckImage, $this->mBgRGB['r'], $this->mBgRGB['g'], $this->mBgRGB['b']);
  return $this->mCheckImage;
 }

 /**
 *
 * @brief  设置图片的干扰像素
 *
 */
 private function SetDisturbColor()
 {
  for ($i=0;$i<=128;$i++)
  {
   $this->mDisturbColor = imagecolorallocate ($this->mCheckImage, rand(0,255), rand(0,255), rand(0,255));
   imagesetpixel($this->mCheckImage,rand(2,128),rand(2,38),$this->mDisturbColor);
  }
 }
 
 /**
  * 设置验证码背景图片的背景颜色的RGB值
  * 
  * @param int $r 如果为null则不修改
  * @param int $g
  * @param int $b
  */
 public function setBgRGB($r = null, $g = null, $b = null)
 {
 	foreach (array('r', 'g', 'b') as $key) {
	 	if (null !== $$key && Lamb_Utils::isInt($$key, true) && $$key >= 0 and $$key <= 255) {
	 		$this->mBgRGB[$key] = $$key;
	 	}
	}
	return $this;
 }

 /**
 *
 * @brief  设置验证码图片的大小
 *
 * @param  $width  宽
 *
 * @param  $height 高 
 *
 */
 public function SetCheckImageWH($width, $height)
 {
 	if (Lamb_Utils::isInt($width, true)) {
  		$this->mCheckImageWidth  = $width;
	}
	
	if (Lamb_Utils::isInt($height, true)) {
  		$this->mCheckImageHeight = $height;
	}
  	return $this;
 }

 /**
 *
 * @brief  在验证码图片上逐个画上验证码
 *
 */
 private function WriteCheckCodeToImage()
 {
  for ($i=0;$i<$this->mCheckCodeNum;$i++)
  {
   $bg_color = imagecolorallocate ($this->mCheckImage, rand(0,255), rand(0,128), rand(0,255));
   $x = floor($this->mCheckImageWidth/$this->mCheckCodeNum)*$i;
   $y = rand(0,$this->mCheckImageHeight-15);
   imagechar ($this->mCheckImage, 5, $x, $y, $this->mCheckCode[$i], $bg_color);
  }
 }

 /**
 *
 * @brief  输出验证码图片
 *
 */
 public function OutCheckImage()
 {
  $this ->OutFileHeader();
  $this ->CreateCheckCode();
  $this ->CreateImage();
  $this ->SetDisturbColor();
  $this ->WriteCheckCodeToImage();
  imagepng($this->mCheckImage);
  imagedestroy($this->mCheckImage);
 }
}