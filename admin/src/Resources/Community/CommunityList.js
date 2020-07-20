import React from 'react';
import { cloneElement } from 'react';
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
  useListContext,
  TopToolbar,
  CreateButton,
  ExportButton,
  Button,
  sanitizeListRestProps,
} from 'react-admin';

import FullNameField from '../User/FullNameField';
import { isAdmin } from '../../auth/permissions';

const CommunityFilter = (props) => (
  <Filter {...props}>
    <TextInput source="name" label="Nom" alwaysOn />
  </Filter>
);
const CommunityPanel = ({ id, record, resource }) => (
  <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);

const ListActions = (props) => {
  const {
    className,
    exporter,
    filters,
    maxResults,
    ...rest
  } = props;
  const {
    currentSort,
    resource,
    displayedFilters,
    filterValues,
    hasCreate,
    basePath,
    selectedIds,
    showFilter,
    total,
  } = useListContext();
  return (
    <TopToolbar className={className} {...sanitizeListRestProps(rest)}>
      {filters && cloneElement(filters, {
        resource,
        showFilter,
        displayedFilters,
        filterValues,
        context: 'button',
      })}
      {
        isAdmin() && <CreateButton basePath={basePath} />
      }
      <ExportButton
        disabled={total === 0}
        resource={resource}
        sort={currentSort}
        filterValues={filterValues}
        maxResults={maxResults}
      />
    </TopToolbar>
  );
};

export const CommunityList = (props) => {
  const translate = useTranslate();

  return (
    <List
      {...props}
      title="Communautés > liste"
      perPage={25}
      filters={<CommunityFilter />}
      sort={{ field: 'originId', order: 'DESC' }}
      actions={<ListActions />}
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
        <EditButton />
      </Datagrid>
    </List>
  );
};
