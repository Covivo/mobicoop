import React from 'react';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { useReference } from 'react-admin';
import { LinearProgress } from '@material-ui/core';

import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@material-ui/core';

import { journeyRenderer } from '../../../../utils/renderers';
import { usernameRenderer } from '../../../../utils/renderers';

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

export const DiariesTable = ({ diaries, version = 'solidary' }) => {
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
                {version === 'solidary' && diary.createdDate
                  ? format(new Date(diary.createdDate), "eee dd LLL HH':'mm", {
                      locale: fr,
                    })
                  : diary.date
                  ? format(new Date(diary.date), "eee dd LLL HH':'mm", {
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
              <TableCell align="center">
                {version === 'solidary' ? diary.actionName : diary.action}
              </TableCell>
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
