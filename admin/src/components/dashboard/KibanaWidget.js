import React, { useState, useEffect } from 'react';
import { Card, CardContent, Grid, Button } from '@material-ui/core';
import { useDataProvider, Title, useTranslate } from 'react-admin';

import { useKibana } from './useKibana';
import getKibanaFilter from './kibanaFilters';
import hasPermission, { isAdmin } from '../../auth/permissions';

const KibanaWidget = ({
  from = 'now-1y',
  width = '100%',
  height = '1200',
  url = process.env.REACT_APP_KIBANA_URL,
}) => {
  const translate = useTranslate();
  const [kibanaStatus, kibanaError] = useKibana();
  const [communitiesList, setCommunitiesList] = useState();
  const kibanaIsAvailable = kibanaStatus === 'CONNECTED';
  console.log('Kibana status : ', kibanaStatus);

  const isCommunityManager =
    hasPermission('community_dashboard_self') && !hasPermission('user_manage');

  // List of communities the user manage
  const dataProvider = useDataProvider();
  // eslint-disable-next-line no-lone-blocks
  {
    useEffect(() => {
      const loadCommunitiesList = () =>
        dataProvider
          .getList('communities/owned', {
            pagination: { page: 1, perPage: 5 },
            sort: { field: 'id', order: 'ASC' },
          })
          .then(
            (result) =>
              result &&
              result.data &&
              result.data.length &&
              setCommunitiesList(result.data.map((c) => c.name))
          );
      isCommunityManager && loadCommunitiesList();
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);
  }

  const dashboard = isAdmin()
    ? process.env.REACT_APP_KIBANA_DASHBOARD
    : process.env.REACT_APP_KIBANA_COMMUNITY_DASHBOARD;

  const styleAdminOrCommunity = isAdmin()
    ? { borderWidth: 0 }
    : { marginTop: '-70px', borderWidth: 0 };
  const filters = isCommunityManager ? getKibanaFilter({ from, communitiesList }) : '';

  const styleVisibleOrNot = kibanaIsAvailable ? {} : { visibility: 'hidden' };

  if (isCommunityManager || isAdmin()) {
    return (
      <>
        {(isAdmin() || hasPermission('dashboard_read')) && kibanaIsAvailable && (
          <Grid container justify="space-between" style={{ marginBottom: 20 }}>
            <Grid item>&nbsp;</Grid>
            <Grid item>
              <Button variant="contained" color="primary" href="https://scope.mobicoop.io/">
                Consulter les autres tableaux de bord
              </Button>
            </Grid>
          </Grid>
        )}
        {kibanaStatus !== 'DISCONNECTED' && url && dashboard ? (
          <iframe
            name="kibana_frame"
            style={{ ...styleVisibleOrNot, ...styleAdminOrCommunity }}
            src={`${url}/app/kibana#/dashboard/${dashboard}?embed=true${filters}`}
            height={height}
            width={width}
            title="Kibana iframe"
          />
        ) : (
          <p>{kibanaError && translate('custom.dashboard.pendingConnectionToKibana')}</p>
        )}
      </>
    );
  }
  return <span>{translate('custom.dashboard.accessDenied')}</span>;
};

export default KibanaWidget;
