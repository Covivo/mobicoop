import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';
import { makeStyles } from '@material-ui/core/styles';
import { useGetList } from 'react-admin';
import {
  Card,
  Grid,
  Avatar,
  LinearProgress,
  Button,
  List,
  ListItem,
  ListItemAvatar,
  ListItemText,
} from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },
}));

const CreateRelatedActionButton = ({ record }) => (
  <Button
    variant="contained"
    color="primary"
    component={Link}
    to={{
      pathname: '/solidary_animations/create',
      state: {
        record: {
          solidary: record.id,
          user: record.solidaryUser.user['@id'],
          actionName: 'solidary_update_progress_manually',
        },
      },
    }}
  >
    Nouvelle action
  </Button>
);

CreateRelatedActionButton.propTypes = {
  record: PropTypes.object.isRequired,
};

const SolidaryAnimation = ({ record }) => {
  const classes = useStyles();
  // List of actions
  const { data, loaded } = useGetList(
    'solidary_animations',
    { page: 1, perPage: 100 },
    { field: 'createdDate', order: 'ASC' },
    { solidary: record.id }
  );
  console.log('data :', data);
  return (
    <Card raised className={classes.card}>
      <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
        <Grid item>
          <b>Dernière action</b>
        </Grid>
        <Grid item>
          <CreateRelatedActionButton record={record} />
        </Grid>
      </Grid>
      {loaded ? (
        data && data.length ? (
          <List>
            <ListItem>
              <ListItemAvatar>
                <Avatar alt="Remy Sharp" src="/static/images/avatar/1.jpg" />
              </ListItemAvatar>
              <ListItemText primary="Solenne Ayzel" secondary="20/02/2020 14:35" />
              <ListItemText
                primary="Contact d'un conducteur par mail"
                secondary="Covoitureur : Umberto Picaldi"
              />
            </ListItem>
            <ListItem>
              {/* eslint-disable-next-line jsx-a11y/anchor-is-valid */}
              <a href="#">Voir toutes les actions</a>
            </ListItem>
          </List>
        ) : (
          <List>Pas encore d'action pour cette demande</List>
        )
      ) : (
        <LinearProgress />
      )}
    </Card>
  );
};

SolidaryAnimation.propTypes = {
  record: PropTypes.object.isRequired,
};

export default SolidaryAnimation;
