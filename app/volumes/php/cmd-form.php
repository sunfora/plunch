<style>
table, tr, td {
    border: 1px solid;
    border-collapse: collapse;
    width: 100;  
}

textarea { 
  width: 100%; 
  border:solid;
}

input {
    width: 100%;
    background-color: green;
    color: white;
    border: solid black;
}
</style>

<form method="post">
    <textarea name="cmd"></textarea>
    <input type="submit" value=">>>" />
</form>

<?
function display(Callable $func): void {
    echo '<hr class="dotted" /><pre>';
    echo $func();
    echo '</pre><hr class="dotted" />';
}
