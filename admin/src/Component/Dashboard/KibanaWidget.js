import React from 'react';
import Card from '@material-ui/core/Card';
import CardContent from '@material-ui/core/CardContent';
import { Title } from 'react-admin';
import { useKibana } from './useKibana';

const KibanaWidget = ({from="now-1y", height="800", width="1000", url=process.env.REACT_APP_KIBANA_URL,dashboard="0a74b5d0-da3a-11e9-8719-5dd244c0aaea"}) => {
    const kibanaStatus = useKibana({username:"elastic", password:"mobiscope", url:url}) // username pwd should be elsewhere
    //const kibanaStatus = true
    return (
        <Card>
            <Title title="Dashboard" />
            <CardContent>
                { kibanaStatus ?
                    <iframe title="dashboard" src={`${url}/app/kibana#/dashboard/${dashboard}?embed=true&_g=(refreshInterval%3A(pause%3A!t%2Cvalue%3A0)%2Ctime%3A(from%3Anow-1y%2Cmode%3Aquick%2Cto%3Anow))`} height={height} width={width}></iframe>
                    : <p>Pas de connexion à Kibana</p>
                }
            </CardContent>
        </Card>
    )
}

export default KibanaWidget

