$(document).ready(function() {
    $("input.all-checker").click(function () {
        items=$(this).parents('td').children('div.columns').find(':checkbox');
        if(this.checked) {
            items.each(function(key,el) {
                el = $(el);
                if(!el.attr('disabled')) {
                    if(!el.attr('checked')) {
                        el.attr('checked', "checked");
                        el.click();
                        el.attr('checked', "checked");
                    }
                }
            });
        }
        else {
            items.each(function(key,el) {
                el = $(el);
                if(!el.attr('disabled')) {
                    el.attr('checked', null);
                }
            });
        }
    });

    $("input.column").click(function() {
        table=$(this).parents("td").children(':checkbox').attr('name');
        column = this.name.split("__")[1];
        $("."+table+"\\."+column).remove();
        ref_key=table+"."+column
        if(!this.checked) {
        /*for(var tbl in constraint_map) {
                if(constraint_map[tbl]['foreign']) {
                    for(var col in constraint_map[tbl]['foreign']) {
                        if(constraint_map[tbl]['foreign'][col]==ref_key) {
                            if($("input[name="+tbl+"__"+col+"]").attr('checked')) {
                                if(window.confirm("Se spoupcem "+table+"."+column+" je v relaci '"+tbl+"."+col+"'. Pokud tento sloupec nezahrnete do revize, je sloupec cizího klíče v tabulce "+tbl+" zbytený. Chete jej odstranit z revize?")) {
                                    $("input[name="+tbl+"__"+col+"]").attr('checked',null);
                                }
                                else {
                                    showWarning(table+"."+column, tbl+"."+col);
                                }

                            }
                            else {
                                
                            }
                        }
                    }
            }
            }*/
        }
        else {
        
            if(constraint_map[table]['foreign'] && constraint_map[table]['foreign'][column]) {
                ref=constraint_map[table]['foreign'][column].split(".");
                ref_table=$("input[name="+ref[0]+"]");
                ref_column=$("input[name="+ref[0]+"__"+ref[1]+"]");
                if(!ref_table.attr('checked') || !ref_column.attr('checked')) {
                    if(window.confirm("Tento sloupec je v relaci s atributem '"+constraint_map[table]['foreign'][column]+"'. Pokud jej chcete vložit do revize, měli byste vložit také odkazovanou tabulku. Chcete ji zařadit do revize?")) {
                        ref_table.attr('checked', 'checked');
                        ref_column.attr('checked', 'checked');
                        //ref_column.click();
                        ref_table.click();
                        ref_table.attr('checked', 'checked');
                    }
                    else {
                        if(!document.getElementById(table+column)) {
                            showWarning(ref[0]+"."+ref[1], table+"."+column);
                        }
                    }
                }
                else {
            //$("."+table+"\\."+column+"."+ref[0]+"\\."+ref[1]).remove();
            }
            }
        //$(".warning."+table+"\\."+column).remove();
        }


    });
    $(".table").click(function() {
        if(this.checked) {
            table=this.name;
            if(constraint_map[table]['primary']) {
                for(column in constraint_map[table]['primary']) {
                    if($("."+table+"\\."+column)) {
                        $("."+table+"\\."+column).remove();
                    }
                }
            }
        }
    });
});

function showWarning(main, ref) {
    
    $(".warnings")
        .append("<div class='flash warning "+main+" "+ref+"'><b>Možná nekonzistence: </b> Sloupec '"+ref+"' je cizím klíčem '"+main+"', který ale není zahrnut do revize.</div>");
}