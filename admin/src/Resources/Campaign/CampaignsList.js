import React, { Fragment, useState } from 'react';

import {
  List,
  Datagrid,
  DateField,
  TextField,
  useTranslate,
  FunctionField,
  SelectField,
  useDataProvider,
  EditButton,
} from 'react-admin';

import MailComposer from '../../components/email/MailComposer';
import FullNameField from '../User/FullNameField';

const CampaignsList = (props) => {
  const translate = useTranslate();
  const dataProvider = useDataProvider();
  const [open, setOpen] = useState(false); // State of the mail modal
  const [campaign, setCampaign] = useState(null);
  const [selectedIdsFormat, setSelectedIdsFormat] = useState([]);

  const HandleModalData = (lid) => {
    dataProvider
      .getOne('campaigns', { id: lid })
      .then(({ data }) => {
        setCampaign(data);
        Promise.all(
          data.deliveries.map((element) =>
            dataProvider
              .getOne('deliveries', { id: element })
              .then(({ data }) => data)
              .catch((error) => {
                console.log("Erreur lors de la campagne d'emailing:", error);
              })
          )
        ).then((data) => {
          setSelectedIdsFormat(data.map((v) => v.user));
          setOpen(true);
        });
      })
      .catch((error) => {
        console.log("Erreur lors de la campagne d'emailing:", error);
      });
  };

  const ButtonCampaign = (props) => {
    //We dont show button if campaign is already send
    if (props.record.status !== 3 && props.record.deliveries.length > 0) {
      return (
        <EditButton
          basePath={'/campaigns/get-on-campaign'}
          onClick={() => HandleModalData(props.record.id)}
        />
      );
    } else return null;
  };

  const statusChoices = [
    { id: 0, name: translate('custom.label.campaign.statusCampaign.init') },
    { id: 1, name: translate('custom.label.campaign.statusCampaign.create') },
    { id: 2, name: translate('custom.label.campaign.statusCampaign.send') },
    { id: 3, name: translate('custom.label.campaign.statusCampaign.archive') },
  ];

  return (
    <Fragment>
      <List {...props} title="Utilisateurs > liste">
        <Datagrid rowClick="edit">
          <TextField source="subject" label={translate('custom.label.campaign.object')} />
          <FunctionField
            label={translate('custom.label.campaign.numberMember')}
            render={(record) => `${record.deliveries.length}`}
          />
          <FullNameField source="user" label={translate('custom.label.campaign.sender')} />
          <SelectField
            source="status"
            abel={translate('custom.label.campaign.state')}
            choices={statusChoices}
          />
          <DateField source="createdDate" label={translate('custom.label.campaign.createdDate')} />
          <DateField source="createdDate" label={translate('custom.label.campaign.updateDate')} />
          <DateField source="createdDate" label={translate('custom.label.campaign.sendDate')} />

          <ButtonCampaign label={translate('custom.label.campaign.resumeCampaign')} />
        </Datagrid>
      </List>

      {open && (
        <MailComposer
          isOpen={open}
          selectedIds={selectedIdsFormat}
          onClose={() => setOpen(false)}
          shouldFetch={false}
          resource={'users'}
          basePath={'/users'}
          filterValues={{}}
          campagneReprise={campaign}
        />
      )}
    </Fragment>
  );
};

export default CampaignsList;
