import React, { useState, useEffect } from 'react';
import BlockIcon from '@material-ui/icons/Block';
import { TableCell, TableRow, Checkbox } from '@material-ui/core';

import {
  List,
  Datagrid,
  TextInput,
  SelectInput,
  ReferenceInput,
  TextField,
  EmailField,
  DateField,
  EditButton,
  BooleanField,
  DatagridBody,
  Filter,
  Button,
  useTranslate,
  useDataProvider,
  AutocompleteInput,
} from 'react-admin';

import EmailComposeButton from '../../components/email/EmailComposeButton';
import ResetButton from '../../components/button/ResetButton';
import isAuthorized from '../../auth/permissions';
import FiltersTraject from './FiltersTraject';

import { DateInput, DateTimeInput } from 'react-admin-date-inputs';
import frLocale from 'date-fns/locale/fr';

const UserList = (props) => {
  const translate = useTranslate();
  const [count, setCount] = useState(0);
  const [communities, setCommunities] = useState();
  const dataProvider = useDataProvider();

  const genderChoices = [
    { id: 1, name: translate('custom.label.user.choices.women') },
    { id: 2, name: translate('custom.label.user.choices.men') },
    { id: 3, name: translate('custom.label.user.choices.other') },
  ];

  useEffect(() => {
    if (localStorage.getItem('id')) {
      dataProvider
        .getList('communities', {
          pagination: { page: 1, perPage: 50 },
          sort: { field: 'id', order: 'ASC' },
        })
        .then((response) => {
          response && setCommunities(response.data);
        });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const BooleanStatusField = ({ record = {}, source }) => {
    const theRecord = { ...record };
    theRecord[source + 'Num'] = !!parseInt(record.status === 1 ? 1 : 0);
    return (
      <BooleanField
        record={theRecord}
        source={source + 'Num'}
        valueLabelTrue="custom.label.user.accountEnabled"
        valueLabelFalse="custom.label.user.accountDisabled"
      />
    );
  };

  const checkValue = ({ selected, record }) => {
    if (record.newsSubscription === false) setCount(selected === false ? count + 1 : count - 1);
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
          comeFrom={0}
          label="Email"
          {...props}
        />

        <ResetButton label="Reset email" {...props} />
        {/* default bulk delete action */}
        {/* <BulkDeleteButton {...props} /> */}
      </>
    );
  };

  const UserFilter = (props) => (
    <Filter {...props}>
      <TextInput source="givenName" label={translate('custom.label.user.givenName')} />
      <TextInput source="familyName" label={translate('custom.label.user.familyName')} alwaysOn />
      <TextInput source="email" label={translate('custom.label.user.email')} alwaysOn />
      <DateInput
        source="createdDate[after]"
        label={translate('custom.label.user.createdDate')}
        options={{ format: 'dd/MM/yyyy', clearable: true }}
        providerOptions={{ locale: frLocale }}
      />
      <DateInput
        source="lastActivityDate[after]"
        label={translate('custom.label.user.lastActivityDate')}
        options={{ format: 'dd/MM/yyyy' }}
        providerOptions={{ locale: frLocale }}
      />
      <DateInput
        source="proposalValidUntil"
        label={translate('custom.label.user.proposalValidUntil')}
        options={{ format: 'dd/MM/yyyy' }}
        providerOptions={{ locale: frLocale }}
      />
      <SelectInput
        source="gender"
        label={translate('custom.label.user.gender')}
        choices={genderChoices}
      />
      <SelectInput
        source="communitiesExclude"
        label={translate('custom.label.user.communitiesExclude')}
        choices={communities}
      />
      <TextInput source="telephone" label={translate('custom.label.user.telephone')} />
      {/* <BooleanInput source="solidary" label={translate('custom.label.user.solidary')} allowEmpty={false} defaultValue={true} /> */}
      <ReferenceInput
        source="homeAddressODTerritory"
        label={translate('custom.label.user.territory')}
        reference="territories"
        allowEmpty={false}
        resettable
        perPage={20}
        filterToQuery={searchText => ({ name: searchText })}
      >
        <AutocompleteInput optionText="name" optionValue="id" />
      </ReferenceInput>
      <FiltersTraject source="trajectCustom" label={translate('custom.label.user.trajet')} />
    </Filter>
  );

  return (
    <List
      {...props}
      title="Utilisateurs > liste"
      perPage={10}
      filters={<UserFilter />}
      sort={{ field: 'id', order: 'ASC' }}
      bulkActionButtons={<UserBulkActionButtons />}
      // exporter={isAuthorized("right_user_assign") ? defaultExporter : false}
      exporter={false}
      hasCreate={isAuthorized('user_create')}
    >
      <MyDatagridUser rowClick="show">
        <TextField source="originId" label={translate('custom.label.user.id')} sortBy="id" />
        <TextField source="givenName" label={translate('custom.label.user.givenName')} />
        <TextField source="familyName" label={translate('custom.label.user.familyName')} />
        <EmailField source="email" label={translate('custom.label.user.email')} />
        <BooleanField
          source="newsSubscription"
          label={translate('custom.label.user.accepteEmail')}
        />
        <BooleanStatusField source="status" label={translate('custom.label.user.accountStatus')} />
        <DateField source="createdDate" label={translate('custom.label.user.createdDate')} />
        <DateField
          source="lastActivityDate"
          label={translate('custom.label.user.lastActivityDate')}
        />
        {isAuthorized('user_update') && <EditButton />}
      </MyDatagridUser>
    </List>
  );
};

export default UserList;
