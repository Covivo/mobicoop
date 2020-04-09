
const getKibanaFilter = ({from, communitiesList}) => {
    
    /* Community filter pattern :
        
            (
                match_phrase:(community_names.keyword:'Club%20de%20foot%20Brest')
            ),
            (
                match_phrase:(community_names.keyword:'Joueur%20du%20dimanche%20')
            )
        
    */
   
   if (!communitiesList || !communitiesList.length) return null

   // Limit to 5 communities max to prevent excessive filter...
   if (communitiesList.lenght > 5) {
       console.error("User manages more than 5 communities. The dashboard may display incomplete data.")
   }
   const filterPattern = communitiesList.slice(0,5).map( c => `( match_phrase:(community_names.keyword:'${encodeURI(c)}') )` ).join(',') 

    const filters = 
    `&_g=
        (
            filters:!
            (
                (
                    '$state':(store:globalState),
                    query:
                    (
                        bool:
                        (
                            minimum_should_match:1,
                            should:!
                            (
                                ${filterPattern}
                            )
                        )
                    )
                )
            ),
            refreshInterval:(pause:!t,value:0),
            time:(from:now-1y,to:now)
        )`
    return filters.replace(/[\n\t ]+/g, '')
}

export default getKibanaFilter
