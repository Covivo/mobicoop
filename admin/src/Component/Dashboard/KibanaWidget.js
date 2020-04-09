import React, {useState, useEffect} from 'react';
import Card from '@material-ui/core/Card';
import CardContent from '@material-ui/core/CardContent';
import { useDataProvider, Title } from 'react-admin';
import { useKibana } from './useKibana';
import isAuthorized from '../../Auth/permissions'
import getKibanaFilter from './kibanaFilters'

const KibanaWidget = ({from="now-1y", width="100%", height="1200", url=process.env.REACT_APP_KIBANA_URL,dashboard=process.env.REACT_APP_KIBANA_DASHBOARD}) => {
    const kibanaStatus = useKibana()
    const [communitiesList, setCommunitiesList] = useState()

    // List of communities the user manage (max 10)
    const dataProvider = useDataProvider()
    useEffect( () => {
        const loadCommunitiesList = () => dataProvider.getList('communities', {pagination:{ page: 1 , perPage: 10 }, sort: { field: 'id', order: 'ASC' }, })
                    .then(result  => result && result.data && result.data.length && setCommunitiesList(result.data.map( c => c.name)))
        loadCommunitiesList()
        }
        , []
    )
    
    // Admin or community ?
    // Full rights granted to   territory_manage
    // Restricted rights for    community_manage (Automatic filter to my list of communities, hiiden with negative margin)
    const style     = isAuthorized("territory_manage") ? {borderWidth:0} : {marginTop:'-70px', borderWidth:0}
    const filters   = getKibanaFilter({from, communitiesList})

    if (isAuthorized("territory_manage") || (filters && isAuthorized("community_manage"))) {
        return (
            <Card>
                <Title title="Dashboard" />
                <CardContent>
                    { kibanaStatus && url && dashboard ?
                        <iframe name="kibana_frame" style={style} src={`${url}/app/kibana#/dashboard/${dashboard}?embed=true${filters}`} 
                            height={height} 
                            width={width}>
                        </iframe>
                        : <p>Pas de connexion à Kibana ..</p>
                    }
                </CardContent>
            </Card>
        )
    } else {
        return (
            <Card>
                <Title title="Dashboard" />
                <CardContent>Chargement des données...</CardContent>
            </Card>
        )
    }

    
}

export default KibanaWidget
