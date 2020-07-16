import React from 'react';
import PropTypes from 'prop-types';
import { makeStyles } from '@material-ui/core/styles';

import { Grid, LinearProgress } from '@material-ui/core';

const useStyles = makeStyles(() => ({
  progress: {
    width: '200px',
  },
}));

export const SolidaryProgress = ({ progression }) => {
  const classes = useStyles();

  return (
    <Grid container direction="column" justify="space-between" alignItems="center" spacing={1}>
      <Grid item className={classes.progress}>
        <LinearProgress variant="determinate" value={progression || 0} />
      </Grid>
      <Grid item>{`Avancement : ${progression || 0}% `}</Grid>
    </Grid>
  );
};

SolidaryProgress.propTypes = {
  progression: PropTypes.number,
};
