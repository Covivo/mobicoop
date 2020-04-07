import React from 'react';
import Card from '@material-ui/core/Card';
import CardContent from '@material-ui/core/CardContent';
import { Title } from 'react-admin';
import { useKibana } from './useKibana';

const KibanaWidget = ({from="now-1y", width="100%", height="1200", url=process.env.REACT_APP_KIBANA_URL,dashboard=process.env.REACT_APP_KIBANA_DASHBOARD}) => {
    const kibanaStatus = useKibana()
    
    return (
        <Card>
            <Title title="Dashboard" />
            <CardContent>
                { kibanaStatus && url && dashboard ?
                    <iframe src={`${url}/app/kibana#/dashboard/${dashboard}?embed=true`} 
                        height={height} 
                        width={width}>
                    </iframe>
                    : <p>Pas de connexion à Kibana ..</p>
                }
            </CardContent>
        </Card>
    )
}

export default KibanaWidget

