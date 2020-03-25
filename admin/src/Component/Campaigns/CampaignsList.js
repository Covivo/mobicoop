import React, {Fragment, useEffect, useState} from 'react';
import {
    List,
    Datagrid,
    DateField,
    TextField,FieldGuesser,useTranslate,FunctionField,SelectField,Button,useDataProvider,useMutation,EditButton
} from 'react-admin';

import UserReferenceField from "../User/UserReferenceField";
import EmailComposeButton from "../Email/EmailComposeButton";
import MailComposer from "../Email/MailComposer";


const CampaignsList = (props) => {

    const translate = useTranslate();
    const dataProvider = useDataProvider();
    const [open, setOpen] = useState(false);    // State of the mail modal
    const [campaign, setCampaign] = useState(null);
    const [selectedIdsFormat, setSelectedIdsFormat] = useState([]);


    const HandleModalData = (lid) => {
        dataProvider.getOne('campaigns',{id: lid} )
            .then( ({ data }) => {
                setCampaign(data)
                let users = [];
                Promise.all(data.deliveries.map(element =>
                    dataProvider.getOne('deliveries',{id: element} )
                      .then( ({ data }) => data )
                        //  users.push(data.user)
                      .catch( error => {
                          console.log("Erreur lors de la campagne d'emailing:", error)
                      })
                )).then(
                  data =>  {
                    setSelectedIdsFormat(data);
                    setOpen(true);
                  }
                );
            })
            .catch( error => {
                console.log("Erreur lors de la campagne d'emailing:", error)
            })

    }

      useEffect(() => {
         if (selectedIdsFormat[0] && selectedIdsFormat.length > 0) {

         }
      });



    const ButtonCampaign = (props) => {
      //We dont show button if campaign is already send
      if (  props.record.status != 3 && props.record.deliveries.length > 0 ){
        return (
             <EditButton basePath={'/campaigns/get-on-campaign'} onClick={ () => HandleModalData(props.record.id) } />
         )
       }else return null
    }

    const statusChoices = [
        { id: 0, name: translate('custom.label.campaign.statusCampaign.init') },
        { id: 1, name: translate('custom.label.campaign.statusCampaign.create') },
        { id: 2, name: translate('custom.label.campaign.statusCampaign.send') },
        { id: 3, name: translate('custom.label.campaign.statusCampaign.archive')  },
    ];

    return(
        <Fragment>
            <List {...props}>
                <Datagrid rowClick="edit">
                    <TextField source="subject" label={translate('custom.label.campaign.object')} />
                    <FunctionField label={translate('custom.label.campaign.numberMember')} render={record => `${record.deliveries.length}` } />
                    <UserReferenceField label={translate('custom.label.campaign.sender')}  source="user" reference="users" />
                    <SelectField source="status" abel={translate('custom.label.campaign.state')} choices={statusChoices} />
                    <DateField source="createdDate" label={translate('custom.label.campaign.createdDate')}/>
                    <DateField source="createdDate" label={translate('custom.label.campaign.updateDate')}/>
                    <DateField source="createdDate" label={translate('custom.label.campaign.sendDate')}/>

                     <ButtonCampaign label={translate('custom.label.campaign.resumeCampaign')} />

                </Datagrid>
            </List>

            <MailComposer
                isOpen={open}
                selectedIds={selectedIdsFormat}
                onClose={()=>setOpen(false)}
                shouldFetch={ false }
                resource={'users'}
                basePath={'/users'}
                filterValues={{}}
                campagneReprise = {campaign}

            />
        </Fragment>
    )
};

export default CampaignsList;
