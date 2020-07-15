import React, { useEffect, useState } from 'react';
import MailIcon from '@material-ui/icons/Mail';
import { Button, useTranslate, useMutation, useDataProvider } from 'react-admin';
import MailComposer from './MailComposer';
import RgpdConsent from './RgpdConsent';
import BlockIcon from '@material-ui/icons/Block';

const EmailComposeButton = ({
  selectedIds,
  resource,
  basePath,
  filterValues,
  canSend,
  comeFrom,
}) => {
  const [open, setOpen] = useState(false); // State of the mail modal
  const [openRgpd, setOpenRgpd] = useState(false); // State of the RGPD modal
  const [rgpdAgree, setRgpdAgree] = useState(false); // State the agreement of the RGPD modal
  const [sendAll, setSendAll] = useState(null); // State the agreement of the RGPD modal
  const shouldFetch = !!Object.keys(filterValues).length;
  const [mutate, { data, loaded }] = useMutation();
  const dataProvider = useDataProvider();
  const [sender, setSender] = useState([]);
  const [campagneInit, setCampagneInit] = useState([]);
  const translate = useTranslate();
  const [selectedIdsFormat, setSelectedIdsFormat] = useState([])

  useEffect(() => {
    let mounted = true;
    localStorage.getItem('id') &&
      dataProvider
        .getOne('users', { id: localStorage.getItem('id') })
        .then(({ data }) => {
          const senderConnecte = {
            replyTo: data.email,
            fromName: data.givenName + ' ' + data.familyName,
            id: data.id,
          };
          if (mounted) {
            setSender([senderConnecte]);
          }
        })
        .catch((error) => {
          console.log("Erreur lors de la recherche de l'utilisateur courant :", error);
        });
    return () => (mounted = false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (comeFrom == 1) {
      setSelectedIdsFormat([]);
      Promise.all(
        selectedIds.map((element) =>
          dataProvider
            .getOne('communityUser', { id: element })
            .then(({ data }) => {
              console.info(data)
              setSelectedIdsFormat((t) => [...t, data.user.id]);
            })
            .catch((error) => {
              console.log('An error occured during user in community retrieving:', error);
            })
        )
      );
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedIds]);
  const campaignCreateParameters = sender[0]
    ? {
      user: sender[0].id,
      name: process.env.REACT_APP_INIT_EMAIL_NAME,
      subject: process.env.REACT_APP_INIT_EMAIL_SUBJECT,

      fromName: sender[0].fromName,
      email: sender[0].replyTo,
      replyTo: sender[0].replyTo,

      body: JSON.stringify([]),
      status: 0,
      medium: '/media/2', // media#2 is email
    }
    : {};
  const handleClick = () => {
    if (rgpdAgree) {
      mutate({
        type: 'create',
        resource: 'campaigns',
        payload: {
          data: campaignCreateParameters,
        },
      });
    } else {
      setOpenRgpd(true);
    }
  };

  const handleClickAll = () => {
    setSendAll(comeFrom);
    if (rgpdAgree) {
      mutate({
        type: 'create',
        resource: 'campaigns',
        payload: {
          data: campaignCreateParameters,
        },
      });
    } else {
      setOpenRgpd(true);
    }
  };

  useEffect(() => {
    if (loaded && data.id) {
      setCampagneInit(data);
      setOpen(true);
    }
  }, [data, loaded]);

  useEffect(() => {
    if (rgpdAgree) {
      handleClick();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rgpdAgree]);

  return (
    <>
      {canSend ? (
        <>
          <Button
            label={
              shouldFetch
                ? translate('custom.email.texte.emailFiltre')
                : translate('custom.email.texte.emailSelect')
            }
            onClick={handleClick}
            startIcon={<MailIcon />}
          />
        </>
      ) : (
          <Button
            label={translate('custom.email.texte.blockUnsubscribe')}
            startIcon={<BlockIcon />}
          />
        )}
      <Button
        label={translate('custom.email.texte.emailAll')}
        onClick={handleClickAll}
        startIcon={<MailIcon />}
      />

      {open && (
        <MailComposer
          isOpen={open}
          sendAll={sendAll}
          selectedIds={comeFrom == 1 ? selectedIdsFormat : selectedIds}
          onClose={() => setOpen(false)}
          shouldFetch={shouldFetch}
          resource={resource}
          basePath={basePath}
          filterValues={filterValues}
          campagneInit={campagneInit}
        />
      )}
      <RgpdConsent
        isOpen={openRgpd}
        onClose={() => setOpenRgpd(false)}
        iAgree={() => setRgpdAgree(true)}
      />
    </>
  );
};

export default EmailComposeButton;
