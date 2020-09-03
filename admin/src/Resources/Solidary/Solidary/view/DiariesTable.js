import React from 'react';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { useTranslate } from 'react-admin';

import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@material-ui/core';

import { usernameRenderer } from '../../../../utils/renderers';
import { SolidaryJourney } from './SolidaryJourney';
import { utcDateFormat } from '../../../../utils/date';

export const DiariesTable = ({ diaries, version = 'solidary' }) => {
  const translate = useTranslate();

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
                  ? utcDateFormat(diary.createdDate, "eee dd LLL HH':'mm", {
                      locale: fr,
                    })
                  : diary.date
                  ? utcDateFormat(diary.date, "eee dd LLL HH':'mm", {
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
                {version === 'solidary'
                  ? translate(`custom.actions.${diary.actionName}`)
                  : translate(`custom.actions.${diary.action}`)}
              </TableCell>
              <TableCell align="center">
                {diary.transporter
                  ? usernameRenderer({ record: diary.transporter })
                  : diary.user
                  ? usernameRenderer({ record: diary.user })
                  : '-'}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );
};
