import React, { useState } from 'react';
import AccountBoxIcon from '@material-ui/icons/AccountBox';
import DriveEtaIcon from '@material-ui/icons/DriveEta';
import VerifiedUserIcon from '@material-ui/icons/VerifiedUser';
import TodayIcon from '@material-ui/icons/Today';
import EventAvailableIcon from '@material-ui/icons/EventAvailable';
import { TableCell, TableRow, Checkbox } from '@material-ui/core';

import {
  Show,
  Tab,
  TabbedShowLayout,
  Datagrid,
  TextField,
  DateField,
  RichTextField,
  DatagridBody,
  SelectField,
  ReferenceArrayField,
  FunctionField,
  useTranslate,
  BooleanField,
  List,
  ImageField,
} from 'react-admin';

import {
  Typography,
  List as ListMaterial,
  ListItem,
  ListItemIcon,
  ListItemText,
  Card,
  CardHeader,
} from '@material-ui/core';

import { addressRenderer } from '../../utils/renderers';
import { validationChoices, statusChoices } from './communityChoices';
import isAuthorized from '../../auth/permissions';
import EmailComposeButton from '../../components/email/EmailComposeButton';
import ResetButton from '../../components/button/ResetButton';
import FullNameField from '../User/FullNameField';
import { ReferenceRecordIdMapper } from '../../components/utils/ReferenceRecordIdMapper';
import { format } from 'date-fns';
import { utcDateFormat } from '../../utils/date';

const Aside = ({ record }) => {
  const translate = useTranslate();

  return (
    <Card style={{ width: 300, marginLeft: '1rem' }}>
      <CardHeader title={<Typography variant="button">Paramètres</Typography>} />
      {record && (
        <ListMaterial>
          <ListItem>
            <ListItemIcon>
              <AccountBoxIcon />
            </ListItemIcon>
            <ListItemText
              primary={
                <Typography variant="body2">
                  {record.membersHidden
                    ? translate('custom.label.community.memberHidden')
                    : translate('custom.label.community.memberVisible')}
                </Typography>
              }
            />
          </ListItem>
          <ListItem>
            <ListItemIcon>
              <DriveEtaIcon />
            </ListItemIcon>
            <ListItemText
              primary={
                <Typography variant="body2">
                  {record.proposalsHidden
                    ? translate('custom.label.community.announceHidden')
                    : translate('custom.label.community.announceVisible')}
                </Typography>
              }
            />
          </ListItem>
          <ListItem>
            <ListItemIcon>
              <VerifiedUserIcon />
            </ListItemIcon>
            <ListItemText
              primary={
                <Typography variant="body2">
                  {validationChoices.find((e) => e.id === (record.validationType || 0)).name}
                </Typography>
              }
            />
          </ListItem>
          <ListItem>
            <ListItemIcon>
              <TodayIcon />
            </ListItemIcon>
            <ListItemText
              primary={
                <Typography variant="body2">
                  {translate('custom.label.community.createdAt') +
                    utcDateFormat(record.createdDate)}
                </Typography>
              }
            />
          </ListItem>
          <ListItem>
            <ListItemIcon>
              <EventAvailableIcon />
            </ListItemIcon>
            <ListItemText
              primary={
                <Typography variant="body2">
                  {record.updatedDate
                    ? translate('custom.label.community.updatedAt') +
                      utcDateFormat(record.updatedDate)
                    : translate('custom.label.community.neverUpdate')}
                </Typography>
              }
            />
          </ListItem>
        </ListMaterial>
      )}
    </Card>
  );
};

const CommunityTitle = ({ record }) => {
  return <span>Communauté {record ? `"${record.name}"` : ''}</span>;
};

export const CommunityShow = (props) => {
  const translate = useTranslate();
  const communityId = props.id;
  const [count, setCount] = useState(0);

  const checkValue = ({ selected, record }) => {
    if (record.user.newsSubscription === false)
      setCount(selected === false ? count + 1 : count - 1);
  };

  const MyDatagridRow = ({ record, resource, id, onToggleItem, children, selected, basePath }) => {
    if (selected && record.newsSubscription === false) setCount(1);
    return (
      <TableRow key={id} hover={true}>
        {/* first column: selection checkbox */}
        <TableCell padding="none">
          <Checkbox
            checked={selected}
            onClick={() => {
              onToggleItem(id);
              checkValue({ selected, record });
            }}
          />
        </TableCell>
        {/* data columns based on children */}
        {React.Children.map(children, (field) => (
          <TableCell key={`${id}-${field.props.source}`}>
            {React.cloneElement(field, {
              record,
              basePath,
              resource,
            })}
          </TableCell>
        ))}
      </TableRow>
    );
  };

  const MyDatagridBody = (props) => <DatagridBody {...props} row={<MyDatagridRow />} />;
  const MyDatagridUser = (props) => <Datagrid {...props} body={<MyDatagridBody />} />;

  const UserBulkActionButtons = (props) => {
    return (
      <>
        <EmailComposeButton
          canSend={isAuthorized('mass_create') && count === 0}
          comeFrom={1}
          label="Email"
          {...props}
        />

        <ResetButton label="Reset email" {...props} />
        {/* default bulk delete action */}
        {/* <BulkDeleteButton {...props} /> */}
      </>
    );
  };

  return (
    <Show {...props} title={<CommunityTitle />} aside={<Aside />}>
      <TabbedShowLayout>
        <Tab label={translate('custom.label.community.detail')}>
          <TextField source="name" label={translate('custom.label.community.name')} />
          <ImageField
            label={translate('custom.label.event.currentImage')}
            source="images[0].versions.square_250"
          />
          <FullNameField source="user" label={translate('custom.label.community.createdBy')} />
          <FunctionField
            source="address"
            label={translate('custom.label.community.adress')}
            render={(r) => addressRenderer(r.address)}
          />
          <TextField source="domain" label={translate('custom.label.community.domainName')} />
          <TextField source="description" label={translate('custom.label.community.description')} />
          <RichTextField
            source="fullDescription"
            label={translate('custom.label.community.descriptionFull')}
          />
          <FunctionField
            label={translate('custom.label.community.numberMember')}
            render={(record) => `${record.communityUsers ? record.communityUsers.length : 0}`}
          />
        </Tab>
        <Tab label={translate('custom.label.community.membersModerator')}>
          <ReferenceRecordIdMapper attribute="communityUsers">
            <ReferenceArrayField
              source="communityUsers"
              reference="community_users"
              addLabel={false}
            >
              <List
                {...props}
                bulkActionButtons={<UserBulkActionButtons />}
                actions={null}
                sort={{ field: 'id', order: 'ASC' }}
                filter={{ is_published: true, community: communityId }}
              >
                <MyDatagridUser>
                  <FullNameField source="user" label={translate('custom.label.community.member')} />
                  <SelectField
                    source="status"
                    label={translate('custom.label.community.status')}
                    choices={statusChoices}
                  />
                  <BooleanField
                    source="user.newsSubscription"
                    label={translate('custom.label.user.accepteEmail')}
                  />
                  <DateField
                    source="createdDate"
                    label={translate('custom.label.community.joinAt')}
                  />
                  <DateField
                    source="acceptedDate"
                    label={translate('custom.label.community.acceptedAt')}
                  />
                  <DateField
                    source="refusedDate"
                    label={translate('custom.label.community.refusedAt')}
                  />
                  {/*
                  Edit and Delete button should be in an Community Edit view
                  <EditButton />
                  <DeleteButton />
                  */}
                </MyDatagridUser>
              </List>
            </ReferenceArrayField>
          </ReferenceRecordIdMapper>
          {/*  <AddNewMemberButton /> should be in an Community Edit view */}
        </Tab>
      </TabbedShowLayout>
    </Show>
  );
};
