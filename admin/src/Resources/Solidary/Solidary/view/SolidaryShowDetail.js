import React from 'react';
import PropTypes from 'prop-types';
import { Card, Grid, makeStyles, List } from '@material-ui/core';

import { Trip } from './Trip';
import { NeedsAndStructure } from './NeedsAndStructure';
import { SolidaryAskRow } from './SolidaryAskRow';

const useStyles = makeStyles((theme) => ({
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },
}));

const SolidaryShowDetail = ({ record }) => {
  const classes = useStyles();

  if (!record) {
    return null;
  }

  const { origin, destination, needs, asksList } = record;

  return (
    <>
      <Card raised className={classes.card}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item lg={8} md={12}>
            <Trip origin={origin} destination={destination} />
          </Grid>
        </Grid>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <NeedsAndStructure record={record} />
          </Grid>
        </Grid>
        <div style={{ height: 500, marginTop: 30, overflowY: 'scroll' }}>
          <List>
            {asksList.map((ask) => (
              <SolidaryAskRow solidary={record} ask={ask} />
            ))}
          </List>
        </div>
      </Card>
    </>
  );
};

SolidaryShowDetail.propTypes = {
  record: PropTypes.object.isRequired,
  history: PropTypes.object.isRequired,
};

export default SolidaryShowDetail;
