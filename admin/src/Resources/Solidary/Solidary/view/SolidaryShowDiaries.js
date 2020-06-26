import React, { useState } from 'react';
import { useGetList, Loading, useTranslate } from 'react-admin';
import { makeStyles, Grid, Button } from '@material-ui/core';

import { SolidaryProgress } from './SolidaryProgress';
import { SolidaryAddDiaryPopup } from './SolidaryAddDiaryPopup';
import { SolidaryChangeProgressPopup } from './SolidaryChangeProgressPopup';
import { DiariesTable } from './DiariesTable';

const useStyles = makeStyles({
  loading: {
    height: '50vh',
  },
});

// Since sorting doesn't work, we sort animation by ourself
const sortByCreatedAt = (a, b) => (new Date(a.createdAt) < new Date(b.createdAt) ? 1 : -1);

export const SolidaryShowDiaries = ({ record }) => {
  const classes = useStyles();
  const translate = useTranslate();
  const [displayAddDiary, setDisplayAddDiary] = useState(false);
  const [displayChangeProgress, setDisplayChangeProgress] = useState(false);

  const { data, ids, loading } = useGetList(
    'solidary_animations',
    { page: 1, perPage: 50 },
    {},
    { solidary: record.id }
  );

  if (loading) {
    return <Loading className={classes.loading} />;
  }

  if (!ids || ids.length === 0) {
    return <center>{translate('custom.solidaryAnimation.noActionForTheMoment')}</center>;
  }

  return (
    <>
      <div style={{ padding: 20 }}>
        <Grid container>
          <Grid item xs={4}>
            <Button
              variant="contained"
              color="primary"
              fullWidth
              onClick={() => setDisplayAddDiary(true)}
            >
              {translate('custom.solidaryAnimation.addAction')}
            </Button>
          </Grid>
          <Grid item xs={4}>
            <SolidaryProgress progression={record.progression} />
          </Grid>
          <Grid item xs={4}>
            <Button
              variant="contained"
              color="primary"
              fullWidth
              onClick={() => setDisplayChangeProgress(true)}
            >
              {translate('custom.solidaryAnimation.changeProgress')}
            </Button>
          </Grid>
        </Grid>
      </div>
      <DiariesTable diaries={ids ? ids.map((id) => data[id]).sort(sortByCreatedAt) : []} />
      {displayAddDiary && record && (
        <SolidaryAddDiaryPopup solidary={record} onClose={() => setDisplayAddDiary(false)} />
      )}
      {displayChangeProgress && record && (
        <SolidaryChangeProgressPopup
          solidary={record}
          onClose={() => setDisplayChangeProgress(false)}
        />
      )}
    </>
  );
};
