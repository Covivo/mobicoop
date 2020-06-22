import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { makeStyles } from '@material-ui/core/styles';
import { useGetList, useTranslate } from 'react-admin';
import { Card, Grid, LinearProgress, List, Button } from '@material-ui/core';
import SolidaryAnimationItem from './SolidaryAnimationItem';
import { SolidaryAddDiaryPopup } from './SolidaryAddDiaryPopup';

const useStyles = makeStyles((theme) => ({
  card: {
    padding: theme.spacing(2, 4, 3),
    marginBottom: '2rem',
  },
}));

// Since sorting doesn't work, we sort animation by ourself
const sortByCreatedAt = (a, b) => (new Date(a.createdAt) < new Date(b.createdAt) ? 1 : -1);

const SolidaryAnimation = ({ record }) => {
  const classes = useStyles();
  const translate = useTranslate();

  const { data, loaded } = useGetList(
    'solidary_animations',
    { page: 1, perPage: 100 },
    { field: 'createdDate', order: 'ASC' },
    { solidary: record.id }
  );

  const animations = Object.values(data) || [];
  const [seeAllAnimations, setSeeAllAnimations] = useState(false);
  const [displayAddDiary, setDisplayAddDiary] = useState(false);

  return (
    <>
      <Card raised className={classes.card}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          <Grid item>
            <b>Dernière action</b>
          </Grid>
          <Grid item>
            <Button
              variant="contained"
              color="primary"
              fullWidth
              onClick={() => setDisplayAddDiary(true)}
            >
              {translate('custom.solidaryAnimation.addAction')}
            </Button>
          </Grid>
        </Grid>
        {loaded ? (
          animations && animations.length ? (
            <>
              <List>
                {animations
                  .filter((a) => seeAllAnimations || a.id === animations[0].id)
                  .sort(sortByCreatedAt)
                  .map((a) => (
                    <SolidaryAnimationItem item={a} />
                  ))}
              </List>
              {!seeAllAnimations && animations.length > 1 && (
                <Button onClick={() => setSeeAllAnimations((a) => !a)}>
                  Voir toutes les actions
                </Button>
              )}
            </>
          ) : (
            <List>Pas encore d&apos;action pour cette demande</List>
          )
        ) : (
          <LinearProgress />
        )}
      </Card>
      {displayAddDiary && record && (
        <SolidaryAddDiaryPopup solidary={record} onClose={() => setDisplayAddDiary(false)} />
      )}
    </>
  );
};

SolidaryAnimation.propTypes = {
  record: PropTypes.object.isRequired,
};

export default SolidaryAnimation;
