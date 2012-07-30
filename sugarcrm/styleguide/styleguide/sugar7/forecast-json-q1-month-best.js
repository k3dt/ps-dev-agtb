{
    "properties": [

    {

        "gauge_target_list":"Array"
        ,
        "title":""
        ,
        "subtitle":""
        ,
        "type":"horizontal group by chart"
        ,
        "legend":"on"
        ,
        "labels":"value"
        ,
        "print":"on"
        ,
        "goal_marker_type": [
            "group"
        ]
        ,
        "goal_marker_color": [
            "#3FB300"
        ]
        ,       "goal_marker_label" : [
        "Quota"
    ]
        ,
        "label_name":"Sales Stage"
        ,
        "value_name":"Amount"
    }

],

    "label": ["Qualified","Proposal","Negotiation","Closed"],
    "color": [

    "#8c2b2b"
    ,
    "#468c2b"
    ,
    "#2b5d8c"
    ,
    "#cd5200"
    ,
    "#e6bf00"
    ,
    "#7f3acd"
    ,
    "#00a9b8"
    ,
    "#572323"
    ,
    "#004d00"
    ,
    "#000087"
    ,
    "#e48d30"
    ,
    "#9fba09"
    ,
    "#560066"
    ,
    "#009f92"
    ,
    "#b36262"
    ,
    "#38795c"
    ,
    "#3D3D99"
    ,
    "#99623d"
    ,
    "#998a3d"
    ,
    "#994e78"
    ,
    "#3d6899"
    ,
    "#CC0000"
    ,
    "#00CC00"
    ,
    "#0000CC"
    ,
    "#cc5200"
    ,
    "#ccaa00"
    ,
    "#6600cc"
    ,
    "#005fcc"

],

    "values": [

    {
        "label": "January",
        "gvalue": "50",
        "gvaluelabel": "50K",
        "values": [0, 60, 0, 0],
        "valuelabels": ["0","60K","0","0"],
        "probability": [0,60,0,0],
        "sales_stage": ["Qualified","Proposal","Negotiation","Closed"],
        "close_date": ["2012-01-01","2012-01-01","2012-01-01","2012-01-01"],
        "links": ["","",""],
        "goalmarkervalue" : [200],
        "goalmarkervaluelabel" : ["200K"]
    }
    ,
    {

        "label": "Febuary",
        "gvalue": "50",
        "gvaluelabel": "50K",
        "values": [0,0,65,0],
        "valuelabels": ["0","0","65K","0"],
        "probability": [0,0,70,0],
        "sales_stage": ["Qualified","Proposal","Negotiation","Closed"],
        "close_date": ["2012-02-01","2012-02-01","2012-02-01","2012-02-01"],
        "links": ["","",""],
        "goalmarkervalue" : [
            200
        ],
        "goalmarkervaluelabel" : [
            "200k"
        ]

    }
    ,
    {
        "label": "March",
        "gvalue": "90",
        "gvaluelabel": "90K",
        "values": [0,0,0,90],
        "valuelabels": ["0","0","0","90K"],
        "sales_stage": ["Qualified","Proposal","Negotiation","Closed"],
        "close_date": ["2012-03-01","2012-03-01","2012-03-01","2012-03-01"],
        "probability": [0,0,0,100],
        "links": ["","",""],
        "goalmarkervalue" : [
            200
        ],
        "goalmarkervaluelabel" : [
            "200K"
        ]

    }



]

}