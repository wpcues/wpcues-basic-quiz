jQuery(document).ready(function($){
if($('#anviprochartholder').length){
var data = [[]];
var ticks=[[]];
var dataset=[];
var datas=anviprochart.data;
var chartoptions=anviprochart.chartoptions;
console.log(chartoptions);
var i=0;var	j=0;
var charttype=parseInt(chartoptions['type'],10);
switch(charttype){
	case 1:
		$.each(datas,function(index,value){
			data[i]=[i,value];
			ticks[i]=[i,index];
			i++;j++;
		});
		var dataset = [{data: data, color: "#FF82FF" }];
		ticks[i]=[i,100];
		var baroptions = {
			series: {
				bars: {
					show: true,
				}
			},
			xaxis: {
				axisLabelUseCanvas: true,
				axisLabelFontSizePixels: 16,
				axisLabelFontFamily: 'Verdana, Arial',
				axisLabelPadding: 10,
				ticks:ticks,
				minx:0,tickLength:0,
			},
			yaxis: {
				axisLabelUseCanvas: true,
				axisLabelFontSizePixels: 16,
				axisLabelFontFamily: 'Verdana, Arial',
				axisLabelPadding: 3,
				miny:0,tickLength:0,
				minTickSize: 1,
			},
			grid: {
				hoverable: true,
				borderWidth: 2,
				backgroundColor: { colors: ["#ffffff", "#EDF5FF"] }
			}
		
		};

		baroptions.bars={align:"center",barWidth:0.75};
		baroptions.xaxis.axisLabel=chartoptions['xaxis'];
		baroptions.yaxis.axisLabel=chartoptions['yaxis'];
		$.plot($('#anviprochartholder'),dataset,baroptions );
		break;
	case 2:
		var colors=["#4572A7","#80699B","#AA4643","#3D96AE","#89A54E","#3D96AE"]
		$.each(datas,function(index,value){
			data[i]={label: index,data: value,color: colors[i]};
			i++;
		});
		$.plot($("#anviprochartholder"), data, {
		series: {
			pie: {
				show: true,
            }
		},
		legend: {
			show: false
			}
		});
		break;
	case 3:
		if(typeof chartoptions.genericoption != 'undefined'){$genericoption=chartoptions.genericoption;}else{$genericoption=0;}
		$.each(datas,function(index,value){
			if($genericoption != 2){data[i]=[i,value];}else{data[i]=[index,value];}
			if($genericoption != 2){ticks[i]=[i,index];}
			i++;j++;
		});
		var dataset = [{data: data, color: "#FF82FF" }];
		if($genericoption != 2){ticks[i]=[i,100];}
		var lineoptions = {
			series: {
				lines: {
					show: true,
				}
			},
			xaxis: {
				axisLabelUseCanvas: true,
				axisLabelFontSizePixels: 16,
				axisLabelFontFamily: 'Verdana, Arial',
				axisLabelPadding: 10,
				minx:0,tickLength:0,
			},
			yaxis: {
				axisLabelUseCanvas: true,
				axisLabelFontSizePixels: 16,
				axisLabelFontFamily: 'Verdana, Arial',
				axisLabelPadding: 3,
				miny:0,tickLength:0,
				minTickSize: 1,
			},
			grid: {
				hoverable: true,
				borderWidth: 2,
				backgroundColor: { colors: ["#ffffff", "#EDF5FF"] }
			}
		};
		if($genericoption !=2){lineoptions.xaxis.ticks=ticks;}
		lineoptions.xaxis.axisLabel=chartoptions['xaxis'];
		lineoptions.yaxis.axisLabel=chartoptions['yaxis'];
		$.plot($('#anviprochartholder'),dataset,lineoptions );
		break;
}


/**/
	
}
});