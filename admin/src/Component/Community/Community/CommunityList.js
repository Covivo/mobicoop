import React from 'react';
import {
    List,
    Datagrid,
    TextInput,
    ShowButton, EditButton,
    FunctionField, TextField, DateField, SelectField,
    Filter,useTranslate
} from 'react-admin';

import UserReferenceField from '../../User/UserReferenceField'

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
      <List {...props} title="Communautés > liste" perPage={ 25 } filters={<CommunityFilter />} sort={{ field: 'originId', order: 'DESC' }}>
        <Datagrid expand={<CommunityPanel />}>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label={translate('custom.label.community.name')}/>
            <DateField source="createdDate" label={translate('custom.label.community.createdDate')}/>
            <FunctionField label={translate('custom.label.community.numberMember')}  render={record => `${record.communityUsers ? record.communityUsers.length : 0}` } />
            <UserReferenceField label={translate('custom.label.community.createdBy')} source="user" reference="users" />
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
  )
};
