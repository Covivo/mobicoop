import React, { useState } from 'react';
import PropTypes from 'prop-types';
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
import CreateRelatedActionButton from './CreateRelatedActionButton';
import SolidaryAnimationItem from './SolidaryAnimationItem';

const useStyles = makeStyles((theme) => ({
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },
}));

const SolidaryAnimation = ({ record }) => {
  const classes = useStyles();
  // List of actions
  const { data, loaded } = useGetList(
    'solidary_animations',
    { page: 1, perPage: 100 },
    { field: 'createdDate', order: 'ASC' },
    { solidary: record.id }
  );
  const animations = Object.values(data) || [];
  console.log('data :', animations);
  const [seeAllAnimations, setSeeAllAnimations] = useState(false);

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
        animations && animations.length ? (
          <List>
            {animations
              .filter((a) => seeAllAnimations || a.id === animations[0].id)
              .map((a) => (
                <SolidaryAnimationItem item={a} />
              ))}
          </List>
        ) : (
          <List>Pas encore d&apos;action pour cette demande</List>
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
