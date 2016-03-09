<?php
/**
 * Lamb Framework
 * @author 小羊
 * @package Lamb_Db
 */
class Lamb_View_PageFunc
{
	/**
	 * @var string
	 */
	public $m_strHtmlFocus;
	/** 
	 * @var string
	 */
	public $m_strHtmlNoFocus;
	/**
	 * @var string
	 */
	public $m_strHtmlMore;
	/**
	 * @var int
	 */
	protected	$m_nPageCount;
	/**
	 * @var int
	 */
	protected	$m_nCurrentPage;
	/**
	 * @var int
	 */
	protected	$m_nPageNum;
	/**
	 * @var int
	 */
	protected	$m_nPageStyle;
	/**
	 * @var string
	 */
	protected	$m_strPageFlag;
	
	/**
	 * 	@param array $aOptions array(page_count=>[int],current_page=>[int],
	 * 	page_num=>[int|default 5],page_style=>[int|default 1],record_count=>[int],
	 * 	focus_html=>[string],nofocus_html=>[string],more_html=>[string|note:for page_template_2])
	 */
	public function __construct($aOptions)
	{
		$aDefault		=	array(
			'page_num'		=>	5,
			'page_style'	=>	1,
			'focus_html'	=>	'',
			'nofocus_html'	=>	'',
			'more_html'		=>	''
		);
		Lamb_Utils::setOptions($aDefault, $aOptions);
		$this->m_nPageCount		=	$aDefault['page_count'];
		$this->m_nCurrentPage	=	max(1,min($aDefault['current_page'],$aDefault['page_count']));
		$this->m_nPageStyle		=	$aDefault['page_style'];
		$this->m_nPageNum		=	$aDefault['page_num'];
		$this->m_strHtmlFocus	=	$aDefault['focus_html'];
		$this->m_strHtmlNoFocus	=	$aDefault['nofocus_html'];
		$this->m_strHtmlMore	=	$aDefault['more_html'];
		$this->m_strPageFlag	=	'#page#';
	}
	
	/**
	 * @return int
	 */
	public function getNextPage()
	{
		return min($this->m_nPageCount, $this->m_nCurrentPage+1);
	}
	
	/**
	 * @return int
	 */
	public function getPreviousPage()
	{
		return max(1, $this->m_nCurrentPage-1);
	}
	
	/**
	 * @return int
	 */
	public function getLastPage()
	{
		return $this->m_nPageCount;
	}
	
	/**
	 * @return int
	 */
	public function getCurrentPage()
	{
		return $this->m_nCurrentPage;
	}
	
	/**
	 * @return string
	 */
	public function showHtmlPage()
	{
		$strHtmlResult		=	'';
		switch ($this->m_nPageStyle)
		{
		case 2:
			$strHtmlResult	=	$this->template_2();
			break;
		case 3:
			$strHtmlResult	=	$this->template_3();
			break;
		default:
			$strHtmlResult	=	$this->template_1();
		}
		return $strHtmlResult;
	}

	/**
	 * @return string
	 */	
	public function template_1()
	{
		$strHtmlNoFocus		=	&$this->m_strHtmlNoFocus;
		$strHtmlFocus		=	&$this->m_strHtmlFocus;
		$strHtmlResult		=	'';
		if ($this->m_nPageNum	==	1){
			return str_replace($this->m_strPageFlag,$this->m_nCurrentPage,$strHtmlNoFocus);
		}
		if($this->m_nPageNum % 2 == 0){
			$this->m_nPageNum	+=	1;
		}
		$nStep				=	(int)($this->m_nPageNum/2);
		$i					=	$this->m_nCurrentPage - $nStep;
		$j					=	$this->m_nCurrentPage + $nStep;
		$nStart = $nEnd = $t = 0;
		if ($i>=1){
			$nStart	=	$i;
			if($j>$this->m_nPageCount){
				$nEnd	=	$this->m_nPageCount;
				$t		=	$j - $this->m_nPageCount;
				$nStart	=	$i - $t;
				if ($nStart<1) $nStart = 1;
			}
			else{
				$nEnd	=	$j;
			}
		}
		else{
			$nStart	=	1;
			if ($j>$this->m_nPageCount){
				$nEnd	=	$this->m_nPageCount;
			}
			else{
				$t		=	$i - 1;
				$nEnd	=	$j - $t;
				if($nEnd>$this->m_nPageCount) $nEnd = $this->m_nPageCount;
			}
		}
		for($_i = $nStart;$_i<=$nEnd;$_i++){
			$strHtmlResult .= str_replace($this->m_strPageFlag,$_i,$_i==$this->m_nCurrentPage?$strHtmlFocus:$strHtmlNoFocus);
		}
		return $strHtmlResult;
	}

	/**
	 * @return string
	 */	
	public function template_2()
	{
		$strHtmlFocus		=	&$this->m_strHtmlFocus;
		$strHtmlNoFocus		=	&$this->m_strHtmlNoFocus;
		$strHtmlMore		=	&$this->m_strHtmlMore;
		$strHtmlResult		=	'';
		if($this->m_nPageCount<=2){
			for($_i=1;$_i<=$this->m_nPageCount;$_i++){
				$strHtmlResult .= str_replace($this->m_strPageFlag,$_i,$_i==$this->m_nCurrentPage?$strHtmlFocus:$strHtmlNoFocus);		
			}
		}
		else{
			$strHtmlResult	=	str_replace($this->m_strPageFlag,1,$this->m_nCurrentPage==1?$strHtmlFocus:$strHtmlNoFocus);
			if($this->m_nPageCount<=$this->m_nPageNum+2){
				for($_i=2,$_j=$this->m_nPageCount-1;$_i<=$_j;$_i++){
					$strHtmlResult .= str_replace($this->m_strPageFlag,$_i,$_i==$this->m_nCurrentPage?$strHtmlFocus:$strHtmlNoFocus);
				}
			}
			else{
				$bEnd	=	false;
				if($this->m_nPageNum % 2 == 0) $this->m_nPageNum += 1;
				$nStep	=	(int)($this->m_nPageNum/2);
				$nStart	=	$nEnd = 0;
				if($this->m_nCurrentPage<$this->m_nPageNum){
					$nStart = 2;
					$nEnd	= $this->m_nPageNum;
					$bEnd	= true;
				}
				else{
					$nStart	= $this->m_nCurrentPage - $nStep;
					$nEnd	= $this->m_nCurrentPage + $nStep;
					if($nEnd>=$this->m_nPageCount-1){
						$_t	= $nEnd - $this->m_nPageCount + 1;
						$nEnd=$this->m_nPageCount - 1;
						$nStart=$nStart - $_t;
						$nStart<=1?$nStart=1:
							$strHtmlResult.=str_replace($this->m_strPageFlag,'more',$strHtmlMore);
					}
					else{ 
						$bEnd= true;
						$strHtmlResult .= str_replace($this->m_strPageFlag,'more',$strHtmlMore);
					}
				}
				for($_i=$nStart;$_i<=$nEnd;$_i++){
					$strHtmlResult	.=	str_replace($this->m_strPageFlag,$_i,$_i==$this->m_nCurrentPage?$strHtmlFocus:$strHtmlNoFocus);
				}
				if ($bEnd){
					$strHtmlResult	.=	str_replace($this->m_strPageFlag,'more',$strHtmlMore);
				}
			}
			$strHtmlResult	.=	str_replace($this->m_strPageFlag,$this->m_nPageCount,$this->m_nCurrentPage==$this->m_nPageCount?$strHtmlFocus:$strHtmlNoFocus);
		}
		return $strHtmlResult;
	}

	/**
	 * @return string
	 */	
	public function template_3()
	{
		$strHtmlResult	=	'';
		for($i=1;$i<=$this->m_nPageCount;$i++){
			$strHtmlResult .= str_replace($this->m_strPageFlag,$i,$i==$this->m_nCurrentPage?$this->m_strHtmlFocus:$this->m_strHtmlNoFocus);
		}
		return $strHtmlResult;
	}
}