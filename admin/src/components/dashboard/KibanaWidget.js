import React, { useState, useEffect } from 'react';
import { Card, CardContent } from '@material-ui/core';
import { useDataProvider, Title, useTranslate } from 'react-admin';
import { useKibana } from './useKibana';
import isAuthorized from '../../auth/permissions';
import getKibanaFilter from './kibanaFilters';

const KibanaWidget = ({
  from = 'now-1y',
  width = '100%',
  height = '1200',
  url = process.env.REACT_APP_KIBANA_URL,
}) => {
  const translate = useTranslate();

  const [kibanaStatus, kibanaError] = useKibana();
  const [communitiesList, setCommunitiesList] = useState();

  // Admin or community ?
  // Full rights granted to   territory_manage
  // Restricted rights for    community_manage (Automatic filter to my list of communities, hidden with negative margin)
  const roles = localStorage.roles.split(',');
  const isCommunityManager =
    isAuthorized('community_dashboard_self') && !isAuthorized('user_manage');
  const isAdmin =
    !roles.includes('ROLE_SUPER_ADMIN') && !roles.includes('ROLE_ADMIN') ? false : true; // a "ROLE_ADMIN" auth_item would be more suitable, but not available yet in the results of /permission API

  // List of communities the user manage
  const dataProvider = useDataProvider();
  {
    useEffect(() => {
      const loadCommunitiesList = () =>
        dataProvider
          .getList('communities', {
            pagination: { page: 1, perPage: 2 },
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
    }, []);
  }

  const dashboard = isAdmin
    ? process.env.REACT_APP_KIBANA_DASHBOARD
    : process.env.REACT_APP_KIBANA_COMMUNITY_DASHBOARD;
  const style = isAdmin ? { borderWidth: 0 } : { marginTop: '-70px', borderWidth: 0 };
  const filters = isCommunityManager ? getKibanaFilter({ from, communitiesList }) : '';

  if (isCommunityManager || isAdmin) {
    return (
      <Card>
        <Title title="Dashboard" />
        <CardContent>
          {kibanaStatus && url && dashboard ? (
            <iframe
              name="kibana_frame"
              style={style}
              src={`${url}/app/kibana#/dashboard/${dashboard}?embed=true${filters}`}
              height={height}
              width={width}
            ></iframe>
          ) : (
            <p>
              {kibanaError ? kibanaError : translate('custom.dashboard.pendingConnectionToKibana')}
            </p>
          )}
        </CardContent>
      </Card>
    );
  } else {
    return (
      <Card>
        <Title title="Dashboard" />
        <CardContent>{translate('custom.dashboard.accessDenied')}</CardContent>
      </Card>
    );
  }
};

export default KibanaWidget;
