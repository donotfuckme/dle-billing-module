    <div class="bt_title">История движения средств</div>
   	 
	<table class="bt_table-normal">
		<tr>
            <td><b>Дата</b></td>
            <td><b>Сумма</b></td>
            <td><b>Остаток на балансе</b></td>
        </tr>
       [history]
		<tr>
            <td>{date=j.m.Y G:i}</td>
            <td>{summa}</td>
            <td>{balance}</td>
        </tr>
		<tr>
            <td colspan="3">&raquo; {comment}</td>
        </tr>
    	[/history]
        [not_history]
        <tr>
            <td colspan="3">&raquo; Записей не найдено</td>
        </tr>
        [/not_history]

	</table>

[paging]
	<div class="bt_pagination">
		[page_link]<a href="{page_num_link}">{page_num}</a>[/page_link]
		[page_this] <strong>{page_num}</strong> [/page_this]
	</div>
[/paging]
		


