import React, { useState } from 'react';
import { useGetList, Loading, useReference, useTranslate } from 'react-admin';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  LinearProgress,
  makeStyles,
  Grid,
  Button,
} from '@material-ui/core';

import { usernameRenderer, journeyRenderer } from '../../../../utils/renderers';
import { SolidaryProgress } from './SolidaryProgress';
import { SolidaryAddDiaryPopup } from './SolidaryAddDiaryPopup';
import { SolidaryChangeProgressPopup } from './SolidaryChangeProgressPopup';

const useStyles = makeStyles({
  loading: {
    height: '50vh',
  },
});

const SolidaryJourney = ({ solidary }) => {
  const { loading, referenceRecord } = useReference({ id: solidary, reference: 'solidaries' });

  if (loading) {
    return <LinearProgress />;
  }

  return referenceRecord &&
    referenceRecord.origin.addressLocality &&
    referenceRecord.destination.addressLocality
    ? journeyRenderer({
        origin: referenceRecord.origin.addressLocality,
        destination: referenceRecord.destination.addressLocality,
      })
    : '-';
};

const DiariesTable = ({ diaries }) => {
  return (
    <TableContainer>
      <Table aria-label="simple table">
        <TableHead>
          <TableRow>
            <TableCell align="center">Date</TableCell>
            <TableCell align="center">Auteur</TableCell>
            <TableCell align="center">Annonce</TableCell>
            <TableCell align="center">Action</TableCell>
            <TableCell align="center">Usager associé</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {(diaries || []).map((diary, i) => (
            <TableRow key={`i${i}`}>
              <TableCell align="center" component="th" scope="row">
                {diary.createdDate
                  ? format(new Date(diary.createdDate), "eee dd LLL HH':'mm", {
                      locale: fr,
                    })
                  : ''}
              </TableCell>
              <TableCell align="center">
                {diary.author ? usernameRenderer({ record: diary.author }) : '-'}
              </TableCell>
              <TableCell align="center">
                <SolidaryJourney solidary={diary.solidary} />
              </TableCell>
              <TableCell align="center">{diary.actionName}</TableCell>
              <TableCell align="center">
                {diary.user ? usernameRenderer({ record: diary.user }) : '-'}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

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
