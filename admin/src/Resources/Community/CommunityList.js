import React from 'react';

import {
  List,
  Datagrid,
  TextInput,
  ShowButton,
  EditButton,
  FunctionField,
  TextField,
  DateField,
  Filter,
  useTranslate,
  ImageField,
} from 'react-admin';

import FullNameField from '../User/FullNameField';
import { hasCommunityEditRight } from '.';
import { isAdmin, isSuperAdmin } from '../../auth/permissions';

const CommunityFilter = (props) => (
  <Filter {...props}>
    <TextInput source="name" label="Nom" alwaysOn />
  </Filter>
);
const CommunityPanel = ({ id, record, resource }) => (
  <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);

export const CommunityList = (props) => {
  const translate = useTranslate();

  return (
    <List
      {...props}
      hasCreate={isAdmin()}
      title="Communautés > liste"
      perPage={25}
      filters={<CommunityFilter />}
      exporter={isSuperAdmin()}
      sort={{ field: 'originId', order: 'DESC' }}
    >
      <Datagrid expand={<CommunityPanel />}>
        <TextField source="originId" label="ID" sortBy="id" />
        <ImageField
          label={translate('custom.label.event.image')}
          source="images[0].versions.square_100"
        />
        <TextField source="name" label={translate('custom.label.community.name')} />
        <DateField source="createdDate" label={translate('custom.label.community.createdDate')} />
        <FunctionField
          label={translate('custom.label.community.numberMember')}
          render={(record) => `${record.communityUsers ? record.communityUsers.length : 0}`}
        />
        <FullNameField source="user" label={translate('custom.label.community.createdBy')} />
        <ShowButton />
        {hasCommunityEditRight() && <EditButton />}
      </Datagrid>
    </List>
  );
};
