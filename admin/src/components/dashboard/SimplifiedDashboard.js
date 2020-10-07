import * as React from 'react';
import { useMemo } from 'react';

import List from '@material-ui/core/List';
import ListItem from '@material-ui/core/ListItem';
import ListItemAvatar from '@material-ui/core/ListItemAvatar';
import ListItemText from '@material-ui/core/ListItemText';
import ListItemSecondaryAction from '@material-ui/core/ListItemSecondaryAction';
import Avatar from '@material-ui/core/Avatar';
import CustomerIcon from '@material-ui/icons/PersonAdd';
import UserIcon from '@material-ui/icons/Person';
import PeopleIcon from '@material-ui/icons/People';
import Grid from '@material-ui/core/Grid';

import { useTranslate, useQueryWithStore } from 'react-admin';
import CardWithIcon from './CardWithIcon';
import UsersChart from './UsersChart';

const SimplifiedDashboard = () => {
  const { loaded, total: usersTotal, data: users } = useQueryWithStore({
    type: 'getList',
    resource: 'users',
    payload: {
      pagination: { page: 1, perPage: 100 },
      sort: { field: 'createdDate', order: 'DESC' },
    },
  });

  const topTenUsers = users ? users.slice(0, 10) : [];

  const {
    loaded: communitiesLoaded,
    total: communitiesTotal,
    data: communities,
  } = useQueryWithStore({
    type: 'getList',
    resource: 'communities',
    payload: {
      pagination: { page: 1, perPage: 10 },
      sort: { field: 'createdDate', order: 'DESC' },
    },
  });

  if (!loaded) return null;

  return (
    <Grid container spacing={3}>
      <Grid item xs={6}>
        <Grid container spacing={3}>
          <Grid item xs={6}>
            <CardWithIcon to="/users" icon={UserIcon} title="Inscrits" subtitle={usersTotal} />
          </Grid>
          <Grid item xs={6}>
            <CardWithIcon
              to="/communities"
              icon={PeopleIcon}
              title="Communautés"
              subtitle={communitiesTotal}
            />
          </Grid>
          <Grid item xs={12}>
            <UsersChart users={users} />
          </Grid>
        </Grid>
      </Grid>
      <Grid item xs={3}>
        <CardWithIcon
          to="/users"
          icon={CustomerIcon}
          title="Derniers inscrits"
          subtitle={users ? users.length : 0}
        >
          <List>
            {users
              ? topTenUsers.map((record) => (
                  <ListItem key={record.id}>
                    <ListItemAvatar>
                      <Avatar src={record.avatars[0]} />
                    </ListItemAvatar>
                    <ListItemText
                      primary={`${record.givenName} ${record.familyName}`}
                      secondary={`Inscrit le ${new Date(record.createdDate).toLocaleDateString()}`}
                    />
                  </ListItem>
                ))
              : null}
          </List>
        </CardWithIcon>
      </Grid>
      <Grid item xs={3}>
        <CardWithIcon
          to="/communities"
          icon={CustomerIcon}
          title="Dernières communautés"
          subtitle={communities ? communities.length : 0}
        >
          <List>
            {communities
              ? communities.map((record) => (
                  <ListItem key={record.id}>
                    <ListItemText
                      primary={record.name}
                      secondary={
                        <span>
                          {record.communityUsers && record.communityUsers.length} inscrits
                        </span>
                      }
                    />
                  </ListItem>
                ))
              : null}
          </List>
        </CardWithIcon>
      </Grid>
    </Grid>
  );
};

export default SimplifiedDashboard;
