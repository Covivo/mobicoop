import React from 'react';
import Paper from '@material-ui/core/Paper';

import {
  List,
  Datagrid,
  TextField,
  DateField,
  ImageField,
  EditButton,
  useTranslate,
} from 'react-admin';

import isAuthorized, { isAdmin, isSuperAdmin } from '../../auth/permissions';

const EventPanel = ({ id, record, resource }) => (
  <Paper style={{ padding: '1rem' }}>
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
    {record.url && (
      <p>
        <a href={record.url}>{record.url}</a>
      </p>
    )}
  </Paper>
);

export const EventList = (props) => {
  const translate = useTranslate();

  return (
    <List {...props} title="Evénement > liste" exporter={isSuperAdmin()} perPage={25}>
      <Datagrid expand={<EventPanel />} rowClick="show">
        <ImageField
          label={translate('custom.label.event.image')}
          source="images[0].versions.square_100"
        />
        <TextField source="name" label={translate('custom.label.event.name')} />
        <DateField source="fromDate" label={translate('custom.label.event.dateStart')} />
        <DateField source="toDate" label={translate('custom.label.event.dateFin')} />
        {isAuthorized('event_update') && <EditButton />}
      </Datagrid>
    </List>
  );
};
