declare namespace App.Domain.Auth.Dto {
export type LoginUserData = {
email: string;
otp: string;
};
export type RegisterUserData = {
name: string;
email: string;
password: string;
};
export type RequestOtpData = {
email: string;
password: string;
};
}
declare namespace App.Domain.Auth.Resources {
export type LoginUserResponseResource = {
success: boolean;
message: string;
token: string | null;
};
export type RegisterUserResponseResource = {
success: boolean;
message: string;
};
export type RequestOtpResponseResource = {
success: boolean;
message: string;
};
export type UserLogoutResponseResource = {
success: boolean;
message: string;
};
}
declare namespace App.Domain.Currency.Resources {
export type CurrencyResource = {
code: string;
name: string | null;
};
export type DenominationResource = {
name: string;
type: string;
value: number;
};
}
declare namespace App.Domain.Wallet.Dto {
export type AddMoneyTransactionItemRequestData = {
denomination_id: string;
quantity: number;
};
export type AddMoneyTransactionRequestData = {
uuid: string;
denominations: Array<App.Domain.Wallet.Dto.AddMoneyTransactionItemRequestData>;
};
export type AddWalletDenominationRequestData = {
currency: string;
name: string;
type: string;
value: number;
};
export type CreateWalletRequestData = {
currency: string;
};
export type WithdrawMoneyTransactionItemRequestData = {
denomination_id: string;
quantity: number;
};
export type WithdrawMoneyTransactionRequestData = {
uuid: string;
denominations: Array<App.Domain.Wallet.Dto.WithdrawMoneyTransactionItemRequestData>;
};
}
declare namespace App.Domain.Wallet.Resource {
export type WithdrawMoneyTransactionResponseResource = {
success: boolean;
message: string;
data: App.Domain.Wallet.Resources.WalletResource;
};
}
declare namespace App.Domain.Wallet.Resources {
export type AddMoneyTransactionResponseResource = {
success: boolean;
message: string;
data: App.Domain.Wallet.Resources.WalletResource;
};
export type AddWalletDenominationResponseResource = {
success: boolean;
data: App.Domain.Wallet.Resources.WalletResource;
};
export type CreateWalletResponseResource = {
success: boolean;
message: string;
data: App.Domain.Wallet.Resources.WalletResource | null;
};
export type DeleteWalletDenominationResponseResource = {
success: boolean;
message: string;
};
export type DeleteWalletResponseResource = {
success: boolean;
message: string;
};
export type TransactionResource = {
id: string;
wallet: App.Domain.Wallet.Resources.WalletResource;
denomination: App.Domain.Wallet.Resources.WalletDenominationResource;
type: string;
quantity: number;
happened_at: string;
};
export type WalletDenominationResource = {
id: string;
name: string;
type: string;
value: number;
quantity: number;
};
export type WalletDetailsResponseResource = {
success: boolean;
data: App.Domain.Wallet.Resources.WalletResource;
};
export type WalletListResponseResource = {
success: boolean;
data: Array<App.Domain.Wallet.Resources.WalletResource>;
};
export type WalletResource = {
id: string;
currency: string;
balance: string;
denominations?: Array<App.Domain.Wallet.Resources.WalletDenominationResource>;
};
}
