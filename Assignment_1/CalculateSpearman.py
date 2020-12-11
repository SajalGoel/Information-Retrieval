def calculateSpearman(d, n):
    num = 6 * d;
    den = n * (n*n - 1);
    res = num / den;
    rho = 1 - res;
    print("The value of rho is: ");
    print(rho);


if __name__ == "__main__":
    d = input("Enter value of d^2: ");
    n = input("Enter value of n: ");
    d = int(d);
    n = int(n);
    calculateSpearman(d,n);

    
