$(document).ready(function(){
	loadMore();
});	

$(window).scroll(function(){
	// ����������ײ�����100����ʱ�� ����������
	if ($(document).height() - $(this).scrollTop() - $(this).height()<100) loadMore();
});


function loadMore()
{
	$.ajax({
		url : 'data.php',
		dataType : 'json',
		success : function(json)
		{
			if(typeof json == 'object')
			{
				var oProduct, $row, iHeight, iTempHeight;
				for(var i=0, l=json.length; i<l; i++)
				{
					oProduct = json[i];
					
					// �ҳ���ǰ�߶���С����, ��������ӵ�����
					iHeight = -1;
					$('#stage li').each(function(){
						iTempHeight = Number( $(this).height() );
						if(iHeight==-1 || iHeight>iTempHeight)
						{
							iHeight = iTempHeight;
							$row = $(this);
						}
					});
					
					
					$item = $('<div><a href="'+oProduct.url+'"><img src="'+oProduct.image+'" border="0" ></a><span>'+oProduct.title+'</span></div>').hide();
					
					$row.append($item);
					$item.fadeIn();
				}
			}
		}
	});
}